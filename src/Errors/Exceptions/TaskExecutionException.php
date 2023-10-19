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

use Ascetik\Fiberloop\Types\FiberLoopExceptionInterface;

/**
 * Exception reserved to any error
 * met during task execution
 *
 * @version 1.0.0
 */
class TaskExecutionException extends \RuntimeException implements FiberLoopExceptionInterface
{
    public function __construct(int $code, string $message, string $file, int $line)
    {
        $this->code = $code;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        return $this;
    }
}
