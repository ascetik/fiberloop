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

namespace Ascetik\Fiberloop\Enums;

/**
 * Enumerate possible states of a FiberTask execution
 *
 * @version 1.0.0
 */
enum TaskExecutionState
{
    case INITIAL;
    case STARTING;
    case RESUMING;
    case COMPLETE;
    case CANCELLED;
    case ABORTED;
    case REJECTED;
    case ONERROR;

    public function isStarted(): bool
    {
        return match ($this) {
            self::STARTING,
            self::RESUMING,
            self::COMPLETE => true,
            default => false
        };
    }
}
