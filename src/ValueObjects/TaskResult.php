<?php

/**
 * This is part of the FiberLoop package.
 *
 * @package    fiberloop
 * @category   Data Transfer Object
 * @license    https://opensource.org/license/mit/  MIT License
 * @copyright  Copyright (c) 2023, Vidda
 * @author     Vidda <vidda@ascetik.fr>
 */

declare(strict_types=1);

namespace Ascetik\Fiberloop\ValueObjects;

use Ascetik\Fiberloop\DTOs\FiberTask;
use Ascetik\Krono\Krono;

/**
 * Task result encapsulation
 *
 * As a task may disappear from tasks stack,
 * an instance of this class is stored to
 * retrieve task details.
 *
 * @version 1.0.0
 */
class TaskResult
{
    public function __construct(
        public readonly string $id,
        public readonly mixed $return,
        public readonly Krono $counter
    ) {
    }

    /**
     * Simple factory
     *
     * @param  FiberTask $task
     *
     * @return self
     */
    public static function createFrom(FiberTask $task): self
    {
        return new self($task->getId(), $task->getReturn(), $task->getCounter());
    }
}
