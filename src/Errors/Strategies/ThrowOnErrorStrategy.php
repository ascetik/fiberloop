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

namespace Ascetik\Fiberloop\Errors\Strategies;

use Ascetik\Fiberloop\FiberLoop;
use Ascetik\Fiberloop\Types\AbstractErrorHandlingStrategy;
use Throwable;

/**
 * Use this strategy to stop the loop
 * and thrown an Exception
 * when a task triggers a script error
 *
 * @version 1.0.0
 */
class ThrowOnErrorStrategy extends AbstractErrorHandlingStrategy
{
    public function react(Throwable $thrown, FiberLoop $loop)
    {
        $loop->abort();
        throw $thrown;
    }
}
