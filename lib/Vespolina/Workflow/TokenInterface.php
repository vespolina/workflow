<?php

namespace Vespolina\Workflow;

interface TokenInterface
{
    /**
     * Set the object
     *
     * @param mixed $object
     */
    function setObject($object);

    /**
     * Return the object
     *
     * @return mixed
     */
    function getObject();
}
