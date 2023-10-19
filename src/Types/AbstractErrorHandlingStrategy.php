<?php

/**
 * This is part of the FiberLoop package.
 *
 * @package    FiberLoop
 * @category   Strategy
 * @license    https://opensource.org/license/mit/  MIT License
 * @copyright  Copyright (c) 2023, Vidda
 * @author     Vidda <vidda@ascetik.fr>
 */

declare(strict_types=1);

namespace Ascetik\Fiberloop\Types;

use Ascetik\Fiberloop\DTOs\FiberTask;
use Ascetik\Fiberloop\Enums\TaskExecutionState;
use Ascetik\Fiberloop\FiberLoop;
use Throwable;

/**
 * Base implementation for any strategy
 * Handling a task triggered error
 *
 * @abstract
 * @version 1.0.0
 */
abstract class AbstractErrorHandlingStrategy
{
    public function __construct(private readonly FiberTask $task)
    {
    }

    protected function id()
    {
        return $this->task->getId();
    }

    protected function taskOnError(): FiberTask
    {
        return $this->task->setExecutionState(TaskExecutionState::ONERROR);
    }

    abstract public function react(Throwable $thrown, FiberLoop $loop);
}
