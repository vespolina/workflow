<?php

namespace Vespolina\Workflow;

class Arc extends Node implements ArcInterface
{
    protected $from;
    protected $to;
    protected $token;

    public function accept(TokenInterface $token)
    {
        try {
            $this->to->accept($token);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

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

    /**
     * Return the from
     *
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
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

    /**
     * Return the to
     *
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    protected function getExpectedInterface(TokenableInterface $tokenable)
    {
        if ($tokenable instanceof TransactionInterface) {
            return 'Vespolina\Workflow\PlaceInterface';
        }

        return 'Vespolina\Workflow\TransactionInterface';
    }
}
