<?php

namespace Ascetik\Fiberloop\Tests\Fakes;

use Ascetik\Fiberloop\FiberLoop;
use Ascetik\Fiberloop\Tests\Fakes\FakeServiceManager;


class Example
{
    public function __construct(private string $name, private ?string $waitFor = null)
    {
    }

    public function process(FiberLoop $loop, FakeServiceManager $service)
    {
        // echo 'processing for ' . $this->name . PHP_EOL;
        if ($this->waitFor) {
            // echo 'having "' . $this->waitFor . '" service to wait for' . PHP_EOL;
            while (!$service->has($this->waitFor)) {
                // echo 'waiting...' . PHP_EOL;
                $loop->next();
            }
        }
        // echo 'all satisfied' . PHP_EOL;
        $service->add($this->name);
        return $this->name;
    }

}
