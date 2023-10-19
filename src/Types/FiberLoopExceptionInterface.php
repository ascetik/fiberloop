<?php

/**
 * This is part of the FiberLoop package.
 *
 * @package    FiberLoop
 * @category   Exception
 * @license    https://opensource.org/license/mit/  MIT License
 * @copyright  Copyright (c) 2023, Vidda
 * @author     Vidda <vidda@ascetik.fr>
 */

declare(strict_types=1);

namespace Ascetik\Fiberloop\Types;

/**
 * Interface reserved to any exception thrown by
 * any part of this package, excluding Excetions
 * thrown by the callback of a task
 *
 * @abstract
 * @version 0.1.0
 */
interface FiberLoopExceptionInterface extends \Throwable
{
}
