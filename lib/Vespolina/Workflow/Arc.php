<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Workflow;

class Arc implements ArcInterface
{
    protected $from;
    protected $to;
    protected $token;
    protected $name;

    /**
     * {@inheritdoc}
     */
    public function accept(TokenInterface $token)
    {
        try {
            return $this->to->accept($token);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setFrom(NodeInterface $tokenable)
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

    /**
     * {@inheritdoc}
     */
    public function setTo(NodeInterface $tokenable)
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

    protected function getExpectedInterface(NodeInterface $tokenable)
    {
        if ($tokenable instanceof TransactionInterface) {
            return 'Vespolina\Workflow\PlaceInterface';
        }

        return 'Vespolina\Workflow\TransactionInterface';
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
