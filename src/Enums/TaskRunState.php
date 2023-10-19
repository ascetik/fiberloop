<?php

/**
 * This is part of the FiberLoop package.
 *
 * @package    FiberLoop
 * @category   Enum
 * @license    https://opensource.org/license/mit/  MIT License
 * @copyright  Copyright (c) 2023, Vidda
 * @author     Vidda <vidda@ascetik.fr>
 */

declare(strict_types=1);

namespace Ascetik\Fiberloop\Enums;

/**
 * These are states an FiberTask can have
 *
 * @version 1.0.0
 */
enum TaskRunState
{
    case RUN;
    case WAIT;
}
