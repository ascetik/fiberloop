<?php

declare(strict_types=1);

namespace Ascetik\Fiberloop\Tests;

use Ascetik\Fiberloop\Enums\TaskExecutionState;
use Ascetik\Fiberloop\Errors\Exceptions\TaskExecutionException;
use Ascetik\Fiberloop\Errors\Exceptions\TaskMaxTriesException;
use Ascetik\Fiberloop\FiberLoop;
use Ascetik\Fiberloop\Tests\Fakes\Example;
use Ascetik\Fiberloop\Tests\Fakes\FakeServiceManager;
use Ascetik\Fiberloop\ValueObjects\TaskReport;
use Exception;
use PHPUnit\Framework\TestCase;

include 'tests/Fakes/functions.php';

class FiberLoopTest extends TestCase
{
    private FiberLoop $loop;

    protected function setUp(): void
    {
        $this->loop = new FiberLoop();
    }

    public function test_fiberloop_with_function()
    {
        // include 'tests/Fakes/functions.php';
        // echo \trying().PHP_EOL;
        $this->assertIsCallable('trying');
        $this->loop->defer('trying');
        $this->loop->defer(fn () => 'and succeded !');
        $this->loop->run();
        $result = $this->loop->getReturns();
        $this->assertSame('i tried and succeded !', implode(' ', $result));
        // var_dump($result);
        $this->assertTrue(true);
    }

    public function test_fiberloop_with_a_class()
    {
        $ex1 = new Example('test1', 'test2');
        $ex2 = new Example('test2', 'test3');
        $ex3 = new Example('test3');
        $service = new FakeServiceManager();
        $this->loop->defer([$ex1, 'process'], ['loop' => $this->loop, "service" => $service])->identifiedBy('test n°1');
        $this->loop->defer([$ex2, 'process'], ['loop' => $this->loop, "service" => $service])->identifiedBy('test n°2');
        $this->loop->defer([$ex3, 'process'], ['loop' => $this->loop, "service" => $service])->identifiedBy('test n°3');

        $this->loop->run();
        // var_dump($service->content());
        $expected = [
            'test n°3' => 'test3',
            'test n°2' => 'test2',
            'test n°1' => 'test1',
        ];
        // var_dump($this->loop->getReturns());
        $this->assertSame(array_values($expected), $service->content());
        $this->assertSame($expected, $this->loop->getReturns());
    }

    // TODO : tests with turns and maxTries

    public function testTaskMaxTries()
    {
        $this->expectException(TaskMaxTriesException::class);
        // $this->expectException(FiberError::class);
        $ex1 = new Example('test1', 'test2');
        $ex2 = new Example('test2', 'test3');
        $ex3 = new Example('test3');
        $service = new FakeServiceManager();
        $this->loop->defer([$ex1, 'process'], ['loop' => $this->loop, "service" => $service])
            ->identifiedBy('test n°1')
            ->setLimit(3);
        $this->loop->defer([$ex2, 'process'], ['loop' => $this->loop, "service" => $service])
            ->identifiedBy('test n°2');
        $this->loop->defer([$ex3, 'process'], ['loop' => $this->loop, "service" => $service])
            ->identifiedBy('test n°3');

        $this->loop->run();
        // var_dump($this->loop->getReturns());
        $this->assertTrue(true);
    }

    public function test_task_self_abortion_after_maxtries()
    {
        $ex1 = new Example('test1', 'test2');
        $ex2 = new Example('test2', 'test3');
        $ex3 = new Example('test3');
        $service = new FakeServiceManager();
        $this->loop->defer([$ex1, 'process'], ['loop' => $this->loop, "service" => $service])
            ->identifiedBy('test n°1')
            ->setLimit(2)
            ->cancelOnReachedLimit();
        $this->loop->defer([$ex2, 'process'], ['loop' => $this->loop, "service" => $service])
            ->identifiedBy('test n°2');
        $this->loop->defer([$ex3, 'process'], ['loop' => $this->loop, "service" => $service])
            ->identifiedBy('test n°3');

        $this->loop->run();
        $expected = [
            'test n°3' => 'test3',
            'test n°2' => 'test2',
        ];
        // var_dump($this->loop->getReturns());
        $this->assertSame(array_values($expected), $service->content());
        $this->assertSame($expected, $this->loop->getReturns());
    }

    public function testTasksReport()
    {
        $ex1 = new Example('test1', 'test2');
        $ex2 = new Example('test2', 'test3');
        $ex3 = new Example('test3');
        $service = new FakeServiceManager();
        $this->loop->debug()
            ->defer([$ex1, 'process'], ['loop' => $this->loop, "service" => $service])->identifiedBy('test n°1')
            ->setLimit(1)
            ->cancelOnReachedLimit();

        $this->loop->defer([$ex2, 'process'], ['loop' => $this->loop, "service" => $service])->identifiedBy('test n°2');
        $this->loop->defer([$ex3, 'process'], ['loop' => $this->loop, "service" => $service])->identifiedBy('test n°3');

        $this->loop->run();

        $reports = $this->loop->getReports();
        $this->assertContainsOnlyInstancesOf(TaskReport::class, $reports);

        $started = $this->loop->getReports('starting');
        $complete = $this->loop->getReports(TaskExecutionState::COMPLETE);
        $aborted = $this->loop->getReports(TaskExecutionState::ABORTED);
        $this->assertCount(3, $started);
        $this->assertCount(2, $complete);
        $this->assertCount(1, $aborted);
    }

    public function testFiberLoopWithLateParameters()
    {
        $ex1 = new Example('test1');
        $service = new FakeServiceManager();

        $this->loop->defer([$ex1, 'process'])
            ->identifiedBy('test n°1');
        $this->loop->run(['loop' => $this->loop, "service" => $service]);

        $expected = [
            'test n°1' => 'test1',
        ];
        $this->assertSame(array_values($expected), $service->content());
        $this->assertSame($expected, $this->loop->getReturns());
    }

    public function testReadmeDemo()
    {
        $services = new FakeServiceManager();
        $this->loop->defer('addDependentService', ['loop' => $this->loop]);
        $this->loop->defer('addMainService');
        // addMainService($services);
        $this->loop->run(['services' => $services]);
        $this->assertTrue($services->has('main'));
        // $this->assertContains('main', $services->content());
    }

    public function testReturnsDemo()
    {
        // include 'tests/Fakes/functions.php';
        $this->loop->defer('firstCall')->identifiedBy('first');
        $this->loop->defer('secondCall')->identifiedBy('second');

        $this->loop->run();

        $results = $this->loop->getReturns();
        // var_dump($results);
        $this->assertCount(2, $results);
        $second = $this->loop->getReturnOf('second');
        $this->assertSame(2, $second);
    }

    public function testEllapsedTime()
    {
        $this->loop->defer(
            function () {
                \Fiber::suspend();
                return 'test';
            }
        )->identifiedBy('suspended');
        $this->loop->defer(function () {
            sleep(1);
            return 'awake';
        })
            ->identifiedBy('sleeping');

        $this->loop->run();
        $sleeping = $this->loop->getElapsedTimeOf('sleeping');
        $suspended = $this->loop->getElapsedTimeOf('suspended') . PHP_EOL;
        $totalTasksTime = $this->loop->getElapsedTimes();
        $total = $this->loop->totalTime();
        $this->assertSame('1s', $sleeping);
        $this->assertTrue($suspended < 500);
        // echo $sleeping.PHP_EOL;
        // echo $suspended.PHP_EOL;
        // echo $total.PHP_EOL;

        // $this->assertTrue($total > 0);
        $this->assertTrue(floatval($total) >= floatval($sleeping));
        $this->assertIsArray($this->loop->getElapsedTimes());
        $this->assertCount(2, $totalTasksTime);
    }
    public function testExceptionThrownOnExecutionError()
    {
        $this->expectException(TaskExecutionException::class);
        $inputs = ['test1', 'test2', 'test3'];
        $this->loop->handleErrors();
        $this->loop->defer(function (array $inputs) {
            return $inputs[4];
        });
        $this->loop->defer(function () {
            return 'finally' . PHP_EOL;
        });

        $this->loop->run(['inputs' => $inputs]);
    }

    public function testExceptionRegisteredOnExecutionError()
    {
        $inputs = ['test1', 'test2', 'test3'];
        $this->loop->handleErrors();
        $this->loop->defer(fn (array $inputs) => $inputs[4], ['inputs' => $inputs])
            ->cancelOnError();
        $this->loop->defer(function () {
            return 'finally';
        });

        $this->loop->run();
        $errors = $this->loop->getErrors();
        $this->assertCount(1, $errors);
        // $first = $errors->shift();
        $first = array_shift($errors);
        $this->assertInstanceOf(TaskExecutionException::class, $first);

        $results = $this->loop->getReturns();
        $this->assertCount(1, $results);
        $this->assertContains('finally', $results);
        $this->assertTrue(true);
    }

    public function testThrowingExceptionFromTaskExecution()
    {
        $this->expectException(Exception::class);
        $inputs = ['test1', 'test2', 'test3'];
        $this->loop->handleErrors();
        $this->loop->defer(function (array $inputs) {
            // return $inputs[4];
            throw new Exception('this is a test');
        });
        $this->loop->defer(function () {
            return 'finally' . PHP_EOL;
        });

        $this->loop->run(['inputs' => $inputs]);
    }

    public function testRegisteringingExceptionFromTaskExecution()
    {
        $this->expectException(Exception::class);
        $inputs = ['test1', 'test2', 'test3'];
        $this->loop->handleErrors();
        $this->loop->defer(
            function () {
                throw new Exception('this is a test');
            }
        )
            ->cancelOnError();
        $this->loop->defer(function () {
            return 'finally' . PHP_EOL;
        });

        $this->loop->run();
        // $errors = $this->loop->getErrors();
        // $this->assertCount(1, $errors);
        // $first = $errors->current();
        // $this->assertInstanceOf(TaskExecutionException::class, $first);

        // $results = $this->loop->getReturns();
        // $this->assertEmpty($results);
        // $this->assertCount(1, $results);
        // $this->assertContains('finally', $results);
        // $this->assertTrue(true);
    }

    // TODO : tests with wait()
}
