<?php

namespace Vespolina\Workflow\Task;

use Bernard\Message\DefaultMessage;
use Bernard\Producer;
use Vespolina\Workflow\TokenInterface;

abstract class BernardQueue extends Queue
{
    protected $producer;

    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    public function execute(TokenInterface $token)
    {
        $queue = $this->getQueueName();
        $this->producer->produce(new DefaultMessage($queue, ['token' => $token]), $queue);

        return true;
    }
} 