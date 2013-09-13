<?php

namespace Vespolina\Workflow;

class Node implements NodeInterface
{
    protected $logger;
    protected $name;
    protected $workflow;

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

    public function setWorkflow($workflow, $logger)
    {
        $this->workflow = $workflow;
        $this->logger = $logger;

        return $this;
    }
}
