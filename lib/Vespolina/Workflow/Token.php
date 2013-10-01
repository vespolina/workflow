<?php

namespace Vespolina\Workflow;

class Token implements TokenInterface
{
    protected $data;
    protected $location;
    protected $object;

    /**
     * Set the data
     *
     * @param string $key
     * @param mixed $data
     * @return self
     */
    public function setData($key, $data)
    {
        $this->data[$key] = $data;

        return $this;
    }

    /**
     * Return the data
     *
     * @param $key
     * @return mixed
     */
    public function getData($key)
    {
        if (!isset($this->data[$key])) {
            return null;
        }

        return $this->data[$key];
    }

    /**
     * Set the location
     *
     * @param NodeInterface $location
     * @return self
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
     * @return self
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
