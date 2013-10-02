<?php

namespace Vespolina\Workflow;

use Psr\Log\LoggerInterface;

interface NodeInterface
{
    /**
     * Set the name
     *
     * @param string $name
     *
     * @return $this
     */
    function setName($name);

    /**
     * Return the name
     *
     * @return string
     */
    function getName();

    /**
     * Set the workflow this node belongs to and that workflow's logger
     *
     * @param Workflow $workflow
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    function setWorkflow(Workflow $workflow, LoggerInterface $logger);
}
