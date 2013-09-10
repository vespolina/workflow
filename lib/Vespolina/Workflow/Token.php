<?php

namespace Vespolina\Workflow;

class Token implements TokenInterface
{
    protected $object;

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
