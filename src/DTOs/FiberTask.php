<?php

/**
 * This is part of the FiberLoop package.
 *
 * @package    FiberLoop
 * @category   Data Transfer Object
 * @license    https://opensource.org/license/mit/  MIT License
 * @copyright  Copyright (c) 2023, Vidda
 * @author     Vidda <vidda@ascetik.fr>
 */

declare(strict_types=1);

namespace Ascetik\Fiberloop\DTOs;

use Ascetik\Krono\Krono;
use Fiber;

/**
 * Handle a task using an identifier and a fiber
 *
 * @version 1.0.0
 */
final class FiberTask
{
    /**
     * Task identifier
     *
     * @var string
     */
    private string $id;

    /**
     * Optionnal parameters for task execution
     *
     * @var array
     */
    private array $parameters;

    /**
     * Limit of tries to avoid infinite
     * loop when task always turns to next
     * without being executed
     *
     * @var int
     */
    private int $limit = 10;

    /**
     * Dynamic turn count, incremented
     * each time Task is asked to execute
     *
     * WAIT state does not increment turn number
     *
     * @var int
     */
    private int $turn = 0;

    /**
     * Task to execute
     *
     * @var Fiber
     */
    private Fiber $fiber;

    /**
     * Current task state, either running or waiting
     *
     * @var TaskRunState
     */
    private TaskRunState $state = TaskRunState::RUN;

    /**
     * Specify behaviour if turn equals limit
     *
     * @var TaskOnExcess
     */
    private TaskOnExcess $limitAction = TaskOnExcess::THROW;

    /**
     * Specify Fiber state
     *
     * @var TaskExecutionState
     */
    private TaskExecutionState $executionState = TaskExecutionState::INITIAL;

    /**
     * Strategy to adopt when an error occurs
     *
     * @var ErrorHandlingStrategy
     */
    private ErrorHandlingStrategy $taskHandlingStrategy;

    /**
     * Calculate task execution ellapsed time
     *
     * @var Krono
     */
    private Krono $counter;

    /**
     * @param  array|callable          $func       callable to execute
     * @param  array<string|int,mixed> $parameters Optionnal parameters
     */
    public function __construct(array|callable $func, array $parameters = [])
    {
        $this->fiber = new Fiber($func);
        $this->id = $this->assignId();
        $this->parameters = $parameters;
        $this->counter = new TimeCounter();
        $this->taskHandlingStrategy = new ThrowOnErrorStrategy($this);
    }


    public function identifiedBy(string|int $id): self
    {
        $this->id = (string) $id;
        return $this;
    }

    public function setLimit(int $tries): self
    {
        $this->limit = $tries;
        return $this;
    }

    public function cancelOnReachedLimit(): self
    {
        $this->limitAction = TaskOnExcess::ABORT;
        return $this;
    }

    public function cancelOnError():self
    {
        $this->taskHandlingStrategy = new CancelOnErrorStrategy($this);
        return $this;
    }

    public function useOnError(ErrorHandlingStrategy $strategy)
    {
        $this->taskHandlingStrategy = $strategy;
        return $this;

    }

    public function toggleRunningState()
    {
        $this->state = $this->state == TaskRunState::RUN
            ? TaskRunState::WAIT
            : TaskRunState::RUN;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setExecutionState(TaskExecutionState $state)
    {
        $this->executionState = $state;
        return $this;
    }

    public function getExecutionState(): TaskExecutionState
    {
        return $this->executionState;
    }

    public function getCounter(): Krono
    {
        return $this->counter;
    }

    public function getErrorStrategy(): ErrorHandlingStrategy
    {
        return $this->taskHandlingStrategy;
    }

    public function getReturn()
    {
        return $this->fiber->getReturn();
    }

    public function run(FiberLoop $loop, array $lateParams = []): mixed
    {

        $this->incrementTries();
        // echo $this->id . ' : ' . $this->turn . PHP_EOL;
        if ($this->turn >= $this->limit) {
            $this->counter->cancel();
            if ($this->limitAction == TaskOnExcess::ABORT) {
                $this->setExecutionState(TaskExecutionState::ABORTED);
                $loop->cancel($this);
                return null;
            }
            // $loop->reportState(TaskExecutionState::REJECTED);
            $this->setExecutionState(TaskExecutionState::REJECTED);
            throw new TaskMaxTriesException($this);
        }

        return $this->process($loop, $lateParams);
    }

    public function process(FiberLoop $loop, array $params = []): mixed
    {
        if ($this->fiber->isStarted() === false) {

            $this->setExecutionState(TaskExecutionState::STARTING);
            $this->counter->start();
            return $this->fiber->start(...[...$this->parameters, ...$params]);
        }

        if ($this->fiber->isTerminated() === false) {
            $this->setExecutionState(TaskExecutionState::RESUMING);
            $this->counter->restart();

            return $this->fiber->resume();
        }
        $this->counter->stop();

        $this->setExecutionState(TaskExecutionState::COMPLETE);
        $loop
            ->addResultFrom($this)
            ->cancel($this);
        return $this->id;
    }

    private function incrementTries()
    {
        if ($this->state == TaskRunState::RUN && $this->executionState->isStarted()) {
            // var_dump($this->executionState->isStarted());
            ++$this->turn;
        }
    }

    private function assignId(): string
    {
        $bytes = random_bytes(8);
        return bin2hex($bytes);
    }
}
