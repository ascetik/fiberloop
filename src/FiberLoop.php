<?php

/**
 * This is part of the FiberLoop package.
 *
 * @package    FiberLoop
 * @license    https://opensource.org/license/mit/  MIT License
 * @copyright  Copyright (c) 2023, Vidda
 * @author     Vidda <vidda@ascetik.fr>
 */

declare(strict_types=1);

namespace Ascetik\Fiberloop;

use Ascetik\Fiberloop\Containers\TaskReportRegistry;
use Ascetik\Krono\Krono;
use Ascetik\Storage\Box;
use Throwable;

/**
 * Handle defered tasks execution
 *
 * @version 0.2.0
 */
class FiberLoop
{
    private Box $tasks;

    private Box $results;

    private Box $errors;

    private TaskReportRegistry $reports;

    private Krono $counter;

    private TaskErrorHandler $errorHandler;

    public function __construct(private bool $debug = false)
    {
        $this->tasks = new Box();
        $this->results = new Box();
        $this->errors = new Box();
        $this->reports = new TaskReportRegistry();
        $this->counter = new TimeCounter();
        $this->errorHandler = new TaskErrorHandler($this);
    }

    public function abort()
    {
        $this->tasks->removeAll($this->tasks);
        return $this;
    }

    public function countTasks()
    {
        return $this->tasks->count();
    }

    public function handleErrors(): self
    {
        $this->errorHandler->on();
        return $this;
    }

    public function registerError(string $id, Throwable $thrown)
    {
        $this->errors->attach($thrown, $id);
    }

    public function defer(array|callable $func, array $params = []): FiberTask
    {
        $task = new FiberTask($func, $params);
        $this->tasks->attach($task);
        return $task;
    }

    public function cancel(FiberTask $task): self
    {
        $this->tasks->detach($task);
        return $this;
    }

    public function next(mixed $value = null)
    {
        return \Fiber::suspend($value);
    }

    public function wait(float $seconds): void
    {
        $this->tasks->current()->toggleRunningState();

        $ellapsed = microtime(true) + $seconds;
        while (microtime(true) < $ellapsed) {
            $this->next();
        }
        $this->tasks->current()->toggleRunningState();
    }

    public function addResultFrom(FiberTask $task)
    {
        $result = TaskResult::createFrom($task);
        $this->results->attach($result);
        return $this;
    }

    public function getReturns(): array
    {
        $returns = [];
        $this->results->each(
            function (TaskResult $result) use (&$returns) {
                $returns[$result->id] = $result->return;
            }
        );
        return $returns;
    }

    public function getReturnOf(string $id): mixed
    {
        $returns = $this->getReturns();
        if (array_key_exists($id, $returns)) {
            return $returns[$id];
        }
        return null;
    }

    public function getEllapsedTimeOf(string $id): ?string
    {
        /** @var ?TaskResult */
        $result = $this->results->find(
            fn (TaskResult $result) => $result->id == $id

        );

        return $result ? $result->counter->ellapsedTime(3) : null;
    }

    public function getEllapsedTimes(): array
    {
        $output = [];
        $this->results->each(
            function (TaskResult $result) use (&$output) {
                $output[$result->id] = $result->counter->ellapsedTime(3);
            }
        );
        return $output;
    }

    public function totalTime(): string
    {
        return $this->counter->ellapsedTime(6);
    }

    public function getReports(string|TaskExecutionState $withState = null): TaskReportRegistry
    {
        if (is_string($withState)) {
            $state = null;
            foreach (TaskExecutionState::cases() as $case) {
                if ($case->name == strtoupper($withState)) {
                    $state = $case;
                    break;
                }
            }
            $withState = $state;
        }
        return $withState
            ? $this->reports->filter(
                fn (TaskReport $report) => $report->state == $withState
            )
            : $this->reports;
    }

    public function getErrors(): array
    {
        $errors = [];
        $this->errors->each(
            function ($exception) use (&$errors) {
                $id = $this->errors->offsetGet($exception);
                $errors[$id] = $exception;
            }
        );
        return $errors;
    }

    public function getErrorIds(): array
    {
        $ids = [];
        $this->errors->each(
            function ($exception) use (&$ids) {
                $ids[] = $this->errors->offsetGet($exception);
            }
        );
        return $ids;
    }

    public function getErrorFor(string $id): ?TaskExecutionException
    {
        return $this->getErrors()[$id] ?? null;
    }

    public function debug(): self
    {
        $this->debug = true;
        return $this;
    }

    public function run(array $lateParams = []): void
    {
        $this->errorHandler->register();
        $this->counter->start();
        while (!$this->tasks->isEmpty()) {
            $this->tasks->each(
                function (FiberTask $task) use ($lateParams) {
                    // $this->errorHandler->handle($task, $this, $lateParams);
                    $task->run($this, $lateParams);
                    $this->reportState($task);

                    /**
                     * J'ai deux sortes d'algorithmes :
                     * - j'en ai un qui fait un try/catch et qui arrete tout
                     * - j'en ai un autre qui se contente de supprimer la tache pour laisser les autres se faire
                     */
                    // try {
                    //     $task->run($this, $lateParams);
                    // } catch (Throwable $e) {
                    //     echo $e->getMessage() . PHP_EOL;
                    //     $this->cancel($task);
                    // }finally{
                    //     $this->reportState($task);

                    // }
                }
            );
        }
        $this->counter->stop();
        $this->errorHandler->restore();
    }

    public function getCurrent(): FiberTask
    {
        return $this->tasks->current();
    }

    public function reportState(FiberTask $task)
    {
        if ($this->debug) {
            $this->reports->push(new TaskReport($task->getId(), $task->getExecutionState()));
        }
    }
}
