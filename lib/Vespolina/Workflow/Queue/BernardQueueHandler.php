<?php

namespace Vespolina\Workflow\Queue;

use Bernard\Producer;
use Vespolina\Workflow\Message\BernardTokenMessage;

class BernardQueueHandler implements QueueHandlerInterface
{
    protected $producer;

    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    public function enqueue($location, $token)
    {
        $this->producer->produce(new BernardTokenMessage($location, $token), $location);

        return true;
    }
}