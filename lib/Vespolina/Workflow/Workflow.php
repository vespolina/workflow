<?php

namespace Vespolina\Workflow;


/**
 * Class Workflow
 * @package Vespolina\Workflow
 */
class Workflow 
{
    protected $nodes;

    public function __construct()
    {
        $input =  new Place();
        $input->setName('workflow.input');
        $this->addNode($input);
        $output =  new Place();
        $output->setName('workflow.output');
        $this->addNode($output);
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
