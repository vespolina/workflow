<?php

namespace Vespolina\Workflow;

interface NodeInterface
{
    /**
     * Set the name
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Return the name
     *
     * @return string
     */
    public function getName();
}
