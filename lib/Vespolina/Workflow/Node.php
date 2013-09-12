<?php

namespace Vespolina\Workflow;

class Node implements NodeInterface
{
    protected $name;

    /**
     * Set the name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Return the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
