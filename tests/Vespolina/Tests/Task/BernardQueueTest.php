<?php

namespace Vespolina\Tests\Task;

use Bernard\Consumer;
use Bernard\Middleware\ErrorLogFactory;
use Bernard\Middleware\FailuresFactory;
use Bernard\Middleware\MiddlewareBuilder;
use Bernard\Producer;
use Bernard\QueueFactory\InMemoryFactory;
use Bernard\Router\SimpleRouter;
use Monolog\Logger;
use Vespolina\Workflow\Token;

class BernardQueueTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->queues = new InMemoryFactory;
        $this->producer = new Producer($this->queues, new MiddlewareBuilder);
    }

    public function testExecuteAndConsume()
    {
        $task = $this->getMock('Vespolina\Workflow\Task\BernardQueue', ['getQueueName', 'testQueue'], [$this->producer]);
        $task->expects($this->any())
            ->method('getQueueName')
            ->will($this->returnValue('TestQueue'));
        $task->expects($this->once())
            ->method('testQueue')
            ->will($this->returnValue(true));

        $rp = new \ReflectionProperty($task, 'logger');
        $rp->setAccessible(true);
        $rp->setValue($task, new Logger('test'));
        $rp->setAccessible(false);

        $token = new Token();
        $token->setData('label', 'test');
        $token->setData('array', ['a' => 'b']);
        $token->setData('object', new TestObject());

        $this->assertTrue($task->accept($token), 'true should be returned when the token is pushed into the queue');

        $envelope = $this->queues->create('TestQueue')->dequeue();
        $message = $envelope->getMessage();
        $this->assertEquals($token, $message->getToken());
        $this->queues->create('TestQueue')->enqueue($envelope);

        $this->router = new SimpleRouter(['TestQueue' => $task]);
        $this->middleware = new MiddlewareBuilder();
        $this->consumer = new Consumer($this->router, $this->middleware);

        $this->consumer->consume($this->queues->create('TestQueue'), ['max-runtime' => 1]);
    }
}

class TestObject
{
    public $data = 42;
}
 