<?php

namespace Vespolina\Workflow;

class Arc extends Node implements ArcInterface
{
    protected $from;
    protected $to;
    protected $token;

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

    public function accept(TokenInterface $token)
    {
        if ($this->token) {
            throw new \InvalidArgumentException('There is already a token in this arc');
        }
        $this->token = $token;

        return true;
    }

    public function forfeit()
    {
        $token = $this->token;
        $this->token = null;

        return $token;
    }

    public function hasToken(TokenInterface $token)
    {
        if ($this->token === $token) {
            return true;
        }

        return false;
    }
}
