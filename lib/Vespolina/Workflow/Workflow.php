<?php

namespace Vespolina\Workflow;

use Psr\Log\LoggerInterface;


/**
 * Class Workflow
 * @package Vespolina\Workflow
 */
class Workflow
{
    protected $input;
    protected $logger;
    protected $nodes;
    protected $output;
    protected $tokens;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->input =  new Place();
        $this->input->setName('workflow.input');
        $this->addNode($this->input);
        $this->output =  new Place();
        $this->output->setName('workflow.output');
        $this->addNode($this->output);
    }

    /**
     * Accept the token workflow which initializes the workflow
     *
     * @param TokenInterface $token
     * @return bool
     */
    public function accept(TokenInterface $token)
    {
        $this->tokens[] = $token;
        if ($this->getInput()->accept($token)) {
            $this->logger->info('Token accepted into workflow, token accepted in input', array('token' => $token));
        }

        return true;
    }

    public function connect(TokenableInterface $from, TokenableInterface $to)
    {
        if ($from instanceof Place && $to instanceof Place) {
            new \InvalidArgumentException('You can only connect a Vespolina\Workflow\TransactionInterface to a Vespolina\Workflow\PlaceInterface');
        }
        if ($from instanceof Transaction && $to instanceof Transaction) {
            new \InvalidArgumentException('You can only connect a Vespolina\Workflow\PlaceInterface to a Vespolina\Workflow\TransactionInterface');
        }
        $arc = $this->createArc($from, $to);

    }

    public function createArc(TokenableInterface $from, TokenableInterface $to)
    {
        $arc = new Arc();
        $arc->setFrom($from);
        $arc->setTo($to);

        return $arc;
    }

    public function getInput()
    {
        return $this->input;
    }

    public function addNode(NodeInterface $node)
    {
        $this->nodes[] = $node;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function getOutput()
    {
        return $this->output;
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
