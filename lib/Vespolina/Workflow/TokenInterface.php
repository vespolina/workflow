<?php

namespace Vespolina\Workflow;

interface TokenInterface
{
    /**
     * Set the location
     *
     * @param NodeInterface $location
     * @return self
     */
    public function setLocation(NodeInterface $location);

    /**
     * Return the location
     *
     * @return NodeInterface
     */
    public function getLocation();

    /**
     * Set the object
     *
     * @param mixed $object
     * @return self
     */
    function setObject($object);

    /**
     * Return the object
     *
     * @return mixed
     */
    function getObject();
}
