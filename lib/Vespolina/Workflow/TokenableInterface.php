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
    public function accept(TokenInterface $token);

    /**
     * Add an incoming arc
     *
     * @param Arc $arc
     */
    public function addInput(Arc $arc);

    /**
     * Return incoming arcs
     *
     * @return array of Arc
     */
    public function getInputs();

    /**
     * Add an outgoing arc
     *
     * @param Arc $arc
     */
    public function addOutput(Arc $arc);

    /**
     * Return outgoing arcs
     *
     * @return array of Arc
     */
    public function getOutputs();

    /**
     * Return the tokens
     *
     * @return array of \Vespolina\Workflow\TokenInterface
     */
    public function getTokens();
}
