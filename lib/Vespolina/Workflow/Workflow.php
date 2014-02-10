<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Workflow;

use Psr\Log\LoggerInterface;
use Vespolina\Workflow\TransactionInterface;

class Workflow
{
    protected $arcs;
    protected $errors;
    protected $finish;
    protected $logger;
    protected $nodes;
    protected $start;
    protected $tokens;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->start = new Place();
        $this->addNode($this->start, 'workflow.start');
        $this->finish = new Place();
        $this->addNode($this->finish, 'workflow.finish');
    }

    /**
     * Accept the token workflow which initializes the workflow
     *
     * @param TokenInterface $token
     * @return boolean
     */
    public function accept(TokenInterface $token)
    {
        $this->addToken($token);
        $this->logger->info('Token accepted into workflow', array('token' => $token));

        return $this->advanceToken($this->getStart(), $token);
    }

    /**
     * Return the arcs
     *
     * @return mixed
     */
    public function getArcs()
    {
        return $this->arcs;
    }

    /**
     * @param string $fromLabel
     * @param string $toLabel
     * @return Arc
     * @throws \InvalidArgumentException
     */
    public function connect($fromLabel, $toLabel)
    {
        if (!isset($this->nodes[$fromLabel])) {
            throw new \InvalidArgumentException("There is no node with the label $fromLabel");
        }
        if (!isset($this->nodes[$toLabel])) {
            throw new \InvalidArgumentException("There is no node with the label $toLabel");
        }
        $fromNode = $this->nodes[$fromLabel];
        $toNode = $this->nodes[$toLabel];
        if ($fromNode instanceof PlaceInterface && $toNode instanceof PlaceInterface) {
            throw new \InvalidArgumentException('You can only connect a Vespolina\Workflow\TransactionInterface to a Vespolina\Workflow\PlaceInterface');
        }
        if ($fromNode instanceof TransactionInterface && $toNode instanceof TransactionInterface) {
            throw new \InvalidArgumentException('You can only connect a Vespolina\Workflow\PlaceInterface to a Vespolina\Workflow\TransactionInterface');
        }

        $arc = new Arc();
        $arc->from = $fromLabel;
        $arc->to = $toLabel;
        $this->arcs[] = $arc;

        $fromNode->addOutput($arc);
        $toNode->addInput($arc);
    }

    /**
     * @param TransactionInterface $from
     * @param TransactionInterface $to
     *
     * @return PlaceInterface
     */
    public function connectThroughPlace(TransactionInterface $from, TransactionInterface $to)
    {
        $place = new Place();
        $this->addNode($place);

        if (!in_array($from, $this->nodes)) {
            $this->addNode($from);
        }
        if (!in_array($to, $this->nodes)) {
            $this->addNode($to);
        }
        $this->connect($from, $place);
        $this->connect($place, $to);

        return $place;
    }

    public function connectToStart(TransactionInterface $tokenable, $name = null)
    {
        if (!in_array($tokenable, $this->nodes)) {
            $this->addNode($tokenable);
        }

        return $this->createArc($this->start, $tokenable);
    }

    public function connectToFinish(TransactionInterface $tokenable)
    {
        if (!in_array($tokenable, $this->nodes)) {
            $this->addNode($tokenable);
        }

        return $this->createArc($tokenable, $this->finish);
    }

    public function createToken(array $data = array())
    {
        $token = new Token();

        foreach ($data as $key => $value) {
            $token->setData($key, $value);
        }
        return $token;
    }

    public function finalize($node, $token)
    {
        if (!$outputs = $node->getOutputs()) {
            return true;
        }
        $node->removeToken($token);
        // single out, no token clone, just update the node location
        if (sizeof($outputs) == 1) {
            $output = array_shift($outputs);
            return $this->advanceToken($output->to, $token);
        }

        // multiple outs, clone for each path, remove original token
        $success = true;
        foreach ($outputs as $output) {
            $newToken = clone $token;
            $this->addToken($newToken);
            $success = $success && $this->advanceToken($output->to, $newToken);
        }
        $this->removeToken($token);

        return $success;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function addNode(NodeInterface $node, $label)
    {
        $this->nodes[$label] = $node;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function getFinish()
    {
        return $this->finish;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function addToken(TokenInterface $token)
    {
        $this->tokens[] = $token;
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

    /**
     * Remove a token from the collection of tokens
     *
     * @param TokenInterface $token
     */
    public function removeToken(TokenInterface $token)
    {
        foreach ($this->tokens as $key => $curToken) {
            if ($token === $curToken) {
                unset($this->tokens[$key]);

                return;
            }
        }
    }

    public function validateWorkflow()
    {
        $this->currentValidationStep('reset');
        $startingNode = $this->getStart();
        $success = $this->traverseNext($startingNode);

        return $success;
    }

    public function traverseNext(NodeInterface $node)
    {
        $this->logger->info(sprintf('Node %s reached, step %s', $node->getName(), $this->currentValidationStep()), array('node' => $node));

        if ($node == $this->getFinish()) {
            return true;
        }
        if (!$arcs = $node->getOutputs()) {
            $this->logger->debug('Missing output arc for ' . $node->getName(), array('node' => $node));

            return false;
        }

        $success = true;
        foreach ($arcs as $arc) {
            if (!$arc->getTo()) {
                $success = false;
                $this->logger->debug(sprintf('Broken arc %s from %s', $arc->getName(), $node->getName()), array('arc' => $arc));
                continue;
            }
            $this->logger->info(sprintf('Traversing arc %s, step %s', $arc->getName(), $this->currentValidationStep()), array('arc' => $arc));
            $success = ($this->traverseNext($arc->getTo()) && $success);
        }

        return $success;
    }

    protected function currentValidationStep($reset = '')
    {
        static $step;

        if ($reset == 'reset') {
            $step = 0;

            return;
        }
        $step++;

        return $step;
    }

    protected function advanceToken($node, $token)
    {
        $message = 'Token advanced into ' . $node->getName();
        $this->logger->info($message, array('token' => $token));
        $token->setLocation($node);
        $node->setWorkflow($this, $this->logger);

        return $node->accept($token);
    }
}
