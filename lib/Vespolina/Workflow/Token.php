<?php

namespace Vespolina\Workflow;

class Token implements TokenInterface
{
    protected $data;
    protected $location;

    /**
     * Set the data
     *
     * @param string $key
     * @param mixed $data
     * @return $this
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
     * {@inheritdoc}
     */
    public function setLocation(NodeInterface $location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocation()
    {
        return $this->location;
    }
}
