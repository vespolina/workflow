<?php

namespace Vespolina\Workflow;


/**
 * Class Workflow
 * @package Vespolina\Workflow
 */
class Workflow extends Node
{
    protected $input;
    protected $nodes;
    protected $output;

    public function __construct()
    {
        $this->input =  new Place();
        $this->input->setName('workflow.input');
        $this->addNode($this->input);
        $this->output =  new Place();
        $this->output->setName('workflow.output');
        $this->addNode($this->output);
    }

    public function getInput()
    {
        return $this->input;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function addNode(NodeInterface $node)
    {
        $this->nodes[] = $node;
    }

    public function getNodes()
    {
        return $this->nodes;
    }
}
