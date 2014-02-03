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
        $this->start->setName('workflow.start');
        $this->addNode($this->start);
        $this->finish = new Place();
        $this->finish->setName('workflow.finish');
        $this->addNode($this->finish);
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
        if ($this->getStart()->accept($token)) {
            return true;
        }

        return false;
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

    public function connect(NodeInterface $from, NodeInterface $to)
    {
        if ($from instanceof PlaceInterface && $to instanceof PlaceInterface) {
            throw new \InvalidArgumentException('You can only connect a Vespolina\Workflow\TransactionInterface to a Vespolina\Workflow\PlaceInterface');
        }
        if ($from instanceof TransactionInterface && $to instanceof TransactionInterface) {
            throw new \InvalidArgumentException('You can only connect a Vespolina\Workflow\PlaceInterface to a Vespolina\Workflow\TransactionInterface');
        }
        if (!in_array($from, $this->nodes)) {
            $this->addNode($from);
        }
        if (!in_array($to, $this->nodes)) {
            $this->addNode($to);
        }

        return $this->createArc($from, $to);
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
        $this->createArc($from, $place);
        $this->createArc($place, $to);

        return $place;
    }

    public function connectToStart(TransactionInterface $tokenable)
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

    public function createArc(NodeInterface $from, NodeInterface $to)
    {
        $arc = new Arc();
        $arc->setFrom($from);
        $arc->setTo($to);
        $this->addArc($arc);

        return $arc;
    }

    public function createToken(array $data = array())
    {
        $token = new Token();

        foreach ($data as $key => $value) {
            $token->setData($key, $value);
        }
        return $token;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function addNode(NodeInterface $node)
    {
        $node->setWorkflow($this, $this->logger);
        $this->nodes[] = $node;
    }

    public function addArc($arc)
    {
        $this->arcs[] = $arc;
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
}
