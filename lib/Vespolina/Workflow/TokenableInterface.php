<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Workflow;

interface TokenableInterface extends NodeInterface
{
    /**
     * Accept the token into the node
     *
     * @param TokenInterface $token
     * @return boolean
     */
    function accept(TokenInterface $token);

    /**
     * The executable functionality. This needs to be implemented for custom places and transactions.
     * This method should not be called directly, but triggered by calling the accept() method
     *
     * @param TokenInterface $token
     * @return mixed
     */
    function execute(TokenInterface $token);

    /**
     * Add an incoming arc
     *
     * @param Arc $arc
     */
    function addInput(Arc $arc);

    /**
     * Return incoming arcs
     *
     * @return Arc[]
     */
    function getInputs();

    /**
     * Add an outgoing arc
     *
     * @param Arc $arc
     */
    function addOutput(Arc $arc);

    /**
     * Return outgoing arcs
     *
     * @return Arc[]
     */
    function getOutputs();

    /**
     * Return the tokens
     *
     * @return \Vespolina\Workflow\TokenInterface[]
     */
    function getTokens();
}
