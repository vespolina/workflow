<?php

namespace Vespolina\Workflow\Task;

use Bernard\Producer;
use Vespolina\Workflow\Message\BernardTokenMessage;
use Vespolina\Workflow\TokenInterface;

abstract class BernardQueue extends Queue
{
    protected $producer;

    public function __construct(Workflow $workflow, Producer $producer)
    {
        $this->producer = $producer;
        $this->workflow = $workflow;
    }

    public function execute(TokenInterface $token)
    {
        $queue = $this->getQueueName();
        $this->producer->produce(new BernardTokenMessage($queue, $token), $queue);

        return true;
    }
} 