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
    const PRE_EXECUTE = 0;
    const EXECUTE = 1;
    const POST_EXECUTE = 2;
    const CLEAN_UP = 3;

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
        $message = 'Token accepted into ' . $this->getName();
        $this->logger->info($message, array('token' => $token));
        $this->tokens[] = $token;
        $token->setLocation($this);

        return $this->processToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function resume(TokenInterface $token)
    {
        $message = 'Token resuming in ' . $this->getName();
        $this->logger->info($message, array('token' => $token));

        return $this->processtoken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(TokenInterface $token)
    {
        throw new \Exception('The execute method needs to be implemented in your class');
    }

    /**
     * {@inheritdoc}
     */
    public function addInput(ArcInterface $arc)
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
    public function addOutput(ArcInterface $arc)
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
     * @param TokenInterface $token
     * @return boolean
     */
    protected function cleanUp(TokenInterface $token)
    {
        return true;
    }

    /**
     * @param TokenInterface $token
     * @return boolean
     */
    protected function postExecute(TokenInterface $token)
    {
        return true;
    }

    /**
     * @param TokenInterface $token
     * @return boolean
     */
    protected function preExecute(TokenInterface $token)
    {
        return true;
    }

    /**
     * @param TokenInterface $token
     * @return boolean
     */
    protected function finalize(TokenInterface $token)
    {
        // no outgoing arcs, means ending node, do not remove token, just return true
        if (!$outputs = $this->getOutputs()) {
            return true;
        }

        // for all other cases remove token from node
        $this->removeToken($token);

        // single output, no token clone, just update the node location
        if (sizeof($outputs) == 1) {
            $output = array_shift($outputs);
            return $output->accept($token);
        }

        // multiple outputs, clone for each path, remove original token
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

        return false;
    }

    private function runStep(TokenInterface $token, $stepName)
    {
        $token->setStatus($this->toStatusConstant($stepName));

        try {
            $success = $this->$stepName($token);
        } catch (\Exception $e) {
            if ($e instanceof ProcessingFailureException) {
                $this->workflow->addError($e->getMessage());
            }

            $success = false;
        }

        return $success;
    }

    private function toStatusConstant($stepName)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $stepName, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        $constantString = implode('_', $ret);

        switch ($constantString) {
            case 'PRE_EXECUTE':
                return self::PRE_EXECUTE;
            case 'EXECUTE':
                return self::EXECUTE;
            case 'POST_EXECUTE':
                return self::POST_EXECUTE;
            case 'CLEAN_UP':
                return self::CLEAN_UP;
        }

        return self::PRE_EXECUTE;
    }

    private function processToken(TokenInterface $token)
    {
        $success = true;
        if ($token->getStatus() <= self::PRE_EXECUTE) {
            if (!$success = $this->runStep($token, 'preExecute')) {
                $this->logStalled($token);
                return $success;
            }
        }

        if ($token->getStatus() <= self::EXECUTE) {
            if (!$success = $this->runStep($token, 'execute')) {
                $this->logStalled($token);
                return $success;
            }
        }

        if ($token->getStatus() <= self::POST_EXECUTE) {
            if (!$success = $this->runStep($token, 'postExecute')) {
                $this->logStalled($token);
                return $success;
            }
        }

        if ($token->getStatus() <= self::CLEAN_UP) {
            if (!$success = $this->runStep($token, 'cleanUp')) {
                $this->logStalled($token);
                return $success;
            }
        }

        return $success;
    }

    private function logStalled(TokenInterface $token)
    {
        $message = sprintf(
                'Token resuming in %s with status %s',
                $this->getName(),
                $token->getStatus()
        );
        $this->logger->info($message, array('token' => $token));
    }
}
