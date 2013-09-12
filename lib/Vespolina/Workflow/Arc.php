<?php

namespace Vespolina\Workflow;

class Arc extends Node
{
    protected $from;
    protected $to;

    public function setFrom(TokenableInterface $tokenable)
    {
        if (isset($this->to)) {
            $expectedInterface = $this->getExpectedInterface($this->to);
            if (!$tokenable instanceof $expectedInterface) {
                throw new \InvalidArgumentException('The "from" node should be an instance of ' . $expectedInterface);
            }
        }
        $tokenable->addOutput($this);
        $this->from = $tokenable;
    }

    public function setTo(TokenableInterface $tokenable)
    {
        if (isset($this->from)) {
            $expectedInterface = $this->getExpectedInterface($this->from);
            if (!$tokenable instanceof $expectedInterface) {
                throw new \InvalidArgumentException('The "to" node should be an instance of ' . $expectedInterface);
            }
        }
        $tokenable->addInput($this);
        $this->to = $tokenable;
    }

    protected function getExpectedInterface(TokenableInterface $tokenable)
    {
        if ($tokenable instanceof TransactionInterface) {
            return 'Vespolina\Workflow\PlaceInterface';
        }

        return 'Vespolina\Workflow\TransactionInterface';
    }
}
