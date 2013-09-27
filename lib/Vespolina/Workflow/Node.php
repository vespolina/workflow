<?php

namespace Vespolina\Workflow;

use Psr\Log\LoggerInterface;

class Node implements NodeInterface
{
    protected $logger;
    protected $name;
    protected $workflow;

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        if (!$this->name) {
            return get_class($this);
        }

        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setWorkflow(Workflow $workflow, LoggerInterface $logger)
    {
        $this->workflow = $workflow;
        $this->logger = $logger;

        return $this;
    }
}
