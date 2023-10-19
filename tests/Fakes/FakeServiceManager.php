<?php

namespace Ascetik\Fiberloop\Tests\Fakes;

class FakeServiceManager
{
    private array $container = [];

    public function add(string $service)
    {
        if (!$this->has($service)) {
            $this->container[] = $service;
        }
    }

    public function has(string $service): bool
    {
        return in_array($service, $this->container);
    }

    public function get(string $service): ?string
    {
        $key = array_search($service, $this->container);
        return $key ? $this->container[$key] : null;
    }

    public function content()
    {
        return $this->container;
    }
}
