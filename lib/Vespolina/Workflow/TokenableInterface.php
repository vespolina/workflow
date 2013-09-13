<?php

namespace Vespolina\Workflow;

interface TokenableInterface extends NodeInterface
{
    /**
     * Accept the token into the node
     *
     * @param TokenInterface $token
     * @return bool
     */
    function accept(TokenInterface $token);

    /**
     * The executable functionality. This needs to be implements for custom places and transactions
     * This method should not be called directly, but triggered calling the accept() method
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
     * @return array of Arc
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
     * @return array of Arc
     */
    function getOutputs();

    /**
     * Return the tokens
     *
     * @return array of \Vespolina\Workflow\TokenInterface
     */
    function getTokens();
}
