<?php

namespace Vespolina\Workflow;

abstract class Tokenable extends Node implements TokenableInterface
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
        $message = 'Token accepted into ' . $this->getName();
        $this->logger->info($message, array('token' => $token));

        $this->tokens[] = $token;
        $token->setLocation($this);
        $this->transferTokenFromInput($token);
        $this->preExecute($token);
        $this->execute($token);
        $this->postExecute($token);
        $this->cleanUp($token);

        return true;
    }

    public function execute(TokenInterface $token)
    {
        throw new \Exception('The execute method needs to be implement in your class');
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

    protected function cleanUp(TokenInterface $token)
    {

    }

    protected function postExecute(TokenInterface $token)
    {

    }

    protected function preExecute(TokenInterface $token)
    {

    }

    protected function transferTokenFromInput(TokenInterface $token)
    {
        $inputs = $this->getInputs();
        foreach ($inputs as $input) {
            if ($input->hasToken($token)) {
                $input->forfeit();
                continue;
            }
        }
    }

    protected function finalize(TokenInterface $token)
    {
        $outputs = $this->getOutputs();
        // single out, no token clone, just update the node location
        if (sizeof($outputs) == 1) {
            $output = array_shift($outputs);
            $output->accept($token);
            $this->removeToken($token);

            return true;
        }

        // multiple outs, clone for each path, remove original token
        foreach ($this->getOutputs() as $output) {
            $newToken = clone $token;
            $this->workflow->addToken($newToken);
            $output->accept($newToken);
        }
        $this->workflow->removeToken($token);
        $this->removeToken($token);
    }

    /**
     * Remove a token from the collection of tokens
     *
     * @param TokenInterface $token
     */
    protected function removeToken(TokenInterface $token)
    {
        foreach ($this->tokens as $key => $curToken) {
            if ($token === $curToken) {
                unset($this->tokens[$key]);

                return true;
            }
        }
    }
}
