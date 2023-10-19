<?php

/**
 * This is part of the FiberLoop package.
 *
 * @package    FiberLoop
 * @category   Handler
 * @license    https://opensource.org/license/mit/  MIT License
 * @copyright  Copyright (c) 2023, Vidda
 * @author     Vidda <vidda@ascetik.fr>
 */

declare(strict_types=1);

namespace Ascetik\Fiberloop\Errors;

use Ascetik\Fiberloop\Errors\Exceptions\TaskExecutionException;
use Ascetik\Fiberloop\FiberLoop;


/**
 * This error handler is only reserved for FiberLoop usage
 * When this handler is on, it replaces preceding
 * registerederror handler and restores it at the end.
 *
 * You can use your own error handler anyway if
 * it works the way you want. Just don't set loop handler on
 *
 * @version 1.0.0
 */
class TaskErrorHandler
{
    /**
     * In-use flag
     *
     * @var bool
     */
    private bool $isOn = false;

    /**
     * Running state flag
     *
     * @var bool
     */
    private bool $isRegistered = false;

    public function __construct(private FiberLoop $loop)
    {
    }

    /**
     * Intercept any error and convert it to
     * an Exception, then give it to task error handling strategy
     *
     * @param  int    $code
     * @param  string $message
     * @param  string $file
     * @param  int    $line
     *
     * @return void
     */
    public function intercept(int $code, string $message, string $file, int $line)
    {
        $thrown = new TaskExecutionException($code, $message, $file, $line);
        $handler = $this->loop->getCurrent()->getErrorStrategy();
        call_user_func([$handler, 'react'], $thrown, $this->loop);
    }

    /**
     * Set in-use flag on to be able to
     * register this error handler during
     * loop
     *
     * @return self
     */
    public function on(): self
    {
        $this->isOn = true;
        return $this;
    }

    /**
     * Set in-use flag off
     *
     * @return self
     */
    public function off():self
    {
        $this->isOn = false;
        return $this;
    }

    /**
     * Register error handler to use
     *
     * @return void
     */
    public function register()
    {
        if ($this->isOn && !$this->isRegistered) {
            set_error_handler([$this, 'intercept']);
            $this->isRegistered = true;
        }
    }

    /**
     * Restore previous handler
     *
     * @return void
     */
    public function restore()
    {
        if ($this->isRegistered) {
            restore_error_handler();
        }
    }
}
