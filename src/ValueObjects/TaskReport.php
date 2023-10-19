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

namespace Ascetik\Fiberloop\ValueObjects;

use Ascetik\Fiberloop\Enums\TaskExecutionState;

/**
 * Encapsulate a task Fiber execution state
 * to notice the loop when debug is on
 *
 * @version 0.1.0
 */
class TaskReport
{
    public function __construct(
        public readonly string $id,
        public readonly TaskExecutionState $state
    ) {
    }
}
