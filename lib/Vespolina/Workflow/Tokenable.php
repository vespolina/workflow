<?php

namespace Vespolina\Workflow;

use Vespolina\Workflow\Exception\ProcessingFailureException;

abstract class Tokenable extends Node implements TokenableInterface
{
    protected $inputs;
    protected $outputs;
    protected $tokens;

    /**
     * {@inheritdoc}
     */
    public function accept(TokenInterface $token)
    {
        $message = 'Token accepted into ' . $this->getName();
        $this->logger->info($message, array('token' => $token));
        $this->tokens[] = $token;
        $token->setLocation($this);

        $success = true;
        try {
            $success = $success && $this->preExecute($token);
            $success = $success && $this->execute($token);
            $success = $success && $this->postExecute($token);
            $success = $success && $this->cleanUp($token);
        } catch (\Exception $e) {
            if ($e instanceof ProcessingFailureException) {
                $this->workflow->addError($e->getMessage());
            }

            return false;
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(TokenInterface $token)
    {
        throw new \Exception('The execute method needs to be implement in your class');
    }

    /**
     * {@inheritdoc}
     */
    public function addInput(Arc $arc)
    {
        $this->inputs[] = $arc;
    }

    /**
     * {@inheritdoc}
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * {@inheritdoc}
     */
    public function addOutput(Arc $arc)
    {
        $this->outputs[] = $arc;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputs()
    {
        return $this->outputs;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    protected function cleanUp(TokenInterface $token)
    {
        return true;
    }

    protected function postExecute(TokenInterface $token)
    {
        return true;
    }

    protected function preExecute(TokenInterface $token)
    {
        return true;
    }

    protected function finalize(TokenInterface $token)
    {
        if (!$outputs = $this->getOutputs()) {
            return true;
        }
        $this->removeToken($token);
        // single out, no token clone, just update the node location
        if (sizeof($outputs) == 1) {
            $output = array_shift($outputs);
            return $output->accept($token);
        }

        // multiple outs, clone for each path, remove original token
        $success = true;
        foreach ($outputs as $output) {
            $newToken = clone $token;
            $this->workflow->addToken($newToken);
            $success = $success && $output->accept($newToken);
        }
        $this->workflow->removeToken($token);

        return $success;
    }

    /**
     * Remove a token from the collection of tokens
     *
     * @param TokenInterface $token
     * @return boolean
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
