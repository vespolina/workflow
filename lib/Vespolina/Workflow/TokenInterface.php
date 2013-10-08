<?php

namespace Vespolina\Workflow;

interface TokenInterface
{
    /**
     * Set the data
     *
     * @param string $key
     * @param mixed $data
     * @return $this
     */
    function setData($key, $data);

    /**
     * Return the data
     *
     * @param $key
     * @return mixed
     */
    function getData($key);

    /**
     * Set the location
     *
     * @param NodeInterface $location
     * @return $this
     */
    function setLocation(NodeInterface $location);

    /**
     * Return the location
     *
     * @return NodeInterface
     */
    function getLocation();

    /**
     * Set the object
     *
     * @param mixed $object
     * @return $this
     */
    function setObject($object);

    /**
     * Return the object
     *
     * @return mixed
     */
    function getObject();
}
