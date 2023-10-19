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

namespace Ascetik\Fiberloop\Errors\Exceptions;

use Ascetik\Fiberloop\DTOs\FiberTask;
use Ascetik\Fiberloop\Types\FiberLoopExceptionInterface;

/**
 * @version 1.0.0
 */
class TaskMaxTriesException extends \Exception implements FiberLoopExceptionInterface
{
    public function __construct(FiberTask $task)
    {
        $this->message = 'Task with id ' . $task->getId() . ' reached ' . $task->getLimit() . ' tries.';
    }
}
