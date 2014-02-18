<?php

namespace Vespolina\Tests\Task;

use Bernard\Consumer;
use Bernard\Middleware\MiddlewareBuilder;
use Bernard\Producer;
use Bernard\QueueFactory\InMemoryFactory;
use Bernard\Router\SimpleRouter;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;
use Vespolina\Workflow\Queue\BernardQueueHandler;
use Vespolina\Workflow\Queue\BernardReceiver;
use Vespolina\Workflow\Token;

class BernardQueueTest extends \PHPUnit_Framework_TestCase
{
    protected $producer;
    protected $queues;

    public function setUp()
    {
        $this->queues = new InMemoryFactory;
        $this->producer = new Producer($this->queues, new MiddlewareBuilder);
    }

    public function testExecuteAndConsume()
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $queueHandler = new BernardQueueHandler($this->producer);

        $workflow = WorkflowCommon::createWorkflow($logger, $queueHandler);

        $token = new Token();
        $token->setLocation('queue_test');
        $token->setData('label', 'test');
        $token->setData('array', ['a' => 'b']);
        $token->setData('object', new TestObject());

        $task = $this->getMock('Vespolina\Workflow\Task\Queue', ['consume']);
        $task->expects($this->once())
            ->method('consume')
            ->with($token)
            ->will($this->returnValue(true));

        $task->setWorkflow($workflow, $logger);
        $workflow->addNode($task, 'queue_test');

        $this->assertTrue($task->accept($token), 'true should be returned when the token is pushed into the queue');

        $envelope = $this->queues->create('queue_test')->dequeue();
        $message = $envelope->getMessage();
        $this->assertEquals($token, $message->getToken());
        $this->queues->create('queue_test')->enqueue($envelope);

        $receiver = new BernardReceiver($workflow);
        $this->router = new SimpleRouter(['queue_test' => $receiver]);
        $this->middleware = new MiddlewareBuilder();
        $this->consumer = new Consumer($this->router, $this->middleware);

        $this->consumer->consume($this->queues->create('queue_test'), ['max-runtime' => 1]);
    }
}

class TestObject
{
    public $data = 42;
}
 