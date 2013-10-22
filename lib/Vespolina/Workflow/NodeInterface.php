<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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
    public function setName($name);

    /**
     * Return the name
     *
     * @return string
     */
    public function getName();

    /**
     * Set the workflow this node belongs to and that workflow's logger
     *
     * @param Workflow $workflow
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setWorkflow(Workflow $workflow, LoggerInterface $logger);
}
