<?php


namespace Vespolina\Workflow\Queue;


use Vespolina\Workflow\Workflow;

class BernardReceiver
{
    protected $workflow;

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function __call($name, $arguments)
    {
        $token = $arguments[0]->getToken();

        return $this->workflow->consumeQueue($token);
    }
}