<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Workflow;

use Psr\Log\LoggerInterface;
use Vespolina\Workflow\TokenInterface;
use Vespolina\Workflow\TransactionInterface;
use Vespolina\Workflow\Queue\QueueHandlerInterface;

class Workflow
{
    protected $arcs;
    protected $errors;
    protected $logger;
    /** @var $nodes NodeInterface[] */
    protected $nodes;
    protected $queueHandler;
    /** @var $tokens TokenInterface[] */
    protected $tokens;
    /** @var $start PlaceInterface */
    protected $start;
    /** @var $finish PlaceInterface */
    protected $finish;

    public function __construct(LoggerInterface $logger, QueueHandlerInterface $queueHandler = null)
    {
        $this->logger = $logger;
        $this->queueHandler = $queueHandler;

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

        return $this->advanceToken('workflow.start', $token);
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
     * @param string $fromLabel
     * @param string $toLabel
     *
     * @return PlaceInterface
     */
    public function connectThroughPlace($fromLabel, $toLabel)
    {
        $place = new Place();
        $placeLabel = 'place_post_' . $fromLabel;
        $this->addNode($place, $placeLabel);

        $this->connect($fromLabel, $placeLabel);
        $this->connect($placeLabel, $toLabel);

        return $place;
    }

    public function connectToStart($label)
    {
        $this->connect('workflow.start', $label);
    }

    public function connectToFinish($label)
    {
        $this->connect($label, 'workflow.finish');
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

    public function addNode(NodeInterface $node, $label)
    {
        $this->nodes[$label] = $node;
    }

    /**
     * @return NodeInterface[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function consumeQueue(TokenInterface $token)
    {
        $location = $token->getLocation();
        $node = $this->nodes[$location];
        $this->addToken($token);

        $message = 'Token is consumed in ' . $location;
        $this->logger->info($message, array('token' => $token));
        $node->setWorkflow($this, $this->logger);
        
        return $node->consume($token);
    }

    public function produceQueue(TokenInterface $token)
    {
        return $this->queueHandler->enqueue($token->getLocation(), $token);
    }

    /**
     * @param TokenInterface $token
     */
    public function addToken(TokenInterface $token)
    {
        $this->tokens[] = $token;
    }

    /**
     * Return the tokens
     *
     * @return TokenInterface[]
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Remove a token from the collection of tokens
     *
     * @param boolean
     */
    public function removeToken(TokenInterface $token)
    {
        foreach ($this->tokens as $key => $curToken) {
            if ($token === $curToken) {
                unset($this->tokens[$key]);

                return true;
            }
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function validateWorkflow()
    {
        $this->currentValidationStep('reset');
        $startingNode = $this->nodes['workflow.start'];
        $success = $this->traverseNext($startingNode);

        return $success;
    }

    /**
     * @param NodeInterface $node
     * @return boolean
     */
    public function traverseNext(NodeInterface $node)
    {
        $this->logger->info(sprintf('Node %s reached, step %s', $node->getName(), $this->currentValidationStep()), array('node' => $node));

        if ($node == $this->nodes['workflow.finish']) {
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

    /**
     * @param string $reset
     * @return mixed
     */
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

    protected function advanceToken($location, $token)
    {
        $message = "Token advanced into $location";
        $this->logger->info($message, array('token' => $token));
        $token->setLocation($location);
        $node = $this->nodes[$location];
        $node->setWorkflow($this, $this->logger);

        return $node->accept($token);
    }
}
