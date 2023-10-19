<?php

use Ascetik\Fiberloop\FiberLoop;
use Ascetik\Fiberloop\Tests\Fakes\FakeServiceManager;


function trying()
{
    return 'i tried';
}

function addMainService(FakeServiceManager $services)
{
    // echo 'registering main service' . PHP_EOL;
    $services->add('main');
}

function addDependentService(FiberLoop $loop,FakeServiceManager $services)
{
    // echo 'trying to add dependant service' . PHP_EOL;
    while(!$services->has('main')){
        // echo 'still no main service' . PHP_EOL;
        $loop->next();
    }
    // echo 'found main service. I can register dependant service' . PHP_EOL;
    $services->add('dependant');
}
function firstCall()
{
    return 1;
}

function secondCall()
{
    return 2;
}

