<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Workflow;

use Psr\Log\LoggerInterface;
use Vespolina\Workflow\Exception\ProcessingFailureException;

class Node implements NodeInterface
{
    protected $logger;
    protected $name;
    /** @var  \Vespolina\Workflow\Workflow */
    protected $workflow;

    protected $inputs;
    protected $outputs;
    protected $tokens;

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

    /**
     * {@inheritdoc}
     */
    public function accept(TokenInterface $token)
    {
        $this->tokens[] = $token;

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
        $this->finalize($token);

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

    /**
     * Remove a token from the collection of tokens
     *
     * @param TokenInterface $token
     * @return boolean
     */
    public function removeToken(TokenInterface $token)
    {
        foreach ($this->tokens as $key => $curToken) {
            if ($token === $curToken) {
                unset($this->tokens[$key]);

                return true;
            }
        }
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
        return $this->workflow->finalize($this, $token);
    }
}
