<?php

namespace Vespolina\Workflow;

class Token implements TokenInterface
{
    protected $location;
    protected $object;

    /**
     * Set the location
     *
     * @param NodeInterface $location
     */
    public function setLocation(NodeInterface $location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Return the location
     *
     * @return NodeInterface
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the object
     *
     * @param mixed $object
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Return the object
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }
}
