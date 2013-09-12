<?php

namespace Vespolina\Workflow;

class Tokenable extends Node implements TokenableInterface
{
    protected $inputs;
    protected $outputs;
    protected $tokens;

    /**
     * Accept the token into the node
     *
     * @param TokenInterface $token
     * @return bool
     */
    public function accept(TokenInterface $token)
    {
        $this->tokens[] = $token;

        return true;
    }

    /**
     * Add an incoming arc
     *
     * @param Arc $arc
     */
    public function addInput(Arc $arc)
    {
        $this->inputs[] = $arc;
    }

    /**
     * Return incoming arcs
     *
     * @return array of Arc
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * Add an outgoing arc
     *
     * @param Arc $arc
     */
    public function addOutput(Arc $arc)
    {
        $this->outputs[] = $arc;
    }

    /**
     * Return outgoing arcs
     *
     * @return array of Arc
     */
    public function getOutputs()
    {
        return $this->outputs;
    }

    /**
     * Return the tokens
     *
     * @return array of \Vespolina\Workflow\TokenInterface
     */
    public function getTokens()
    {
        return $this->tokens;
    }
}
