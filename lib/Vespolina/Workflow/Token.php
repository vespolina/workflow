<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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

    /**
     * {@inheritdoc}
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getObject()
    {
        return $this->object;
    }
}
