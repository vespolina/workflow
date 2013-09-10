<?php

namespace Vespolina\Workflow;

class Arc extends Node
{
    protected $input;

    protected $output;

    public function setInput(NodeInterface $node)
    {
        if (isset($this->output)) {
            $expectedInterface = $this->getExpectedInterface($this->output);
            if (!$node instanceof $expectedInterface) {
                throw new \InvalidArgumentException("The arc output should be an instance of " . $expectedInterface);
            }
        }

        $this->input = $node;
    }

    public function setOutput(NodeInterface $node)
    {
        if (isset($this->input)) {
            $expectedInterface = $this->getExpectedInterface($this->input);
            if (!$node instanceof $expectedInterface) {
                throw new \InvalidArgumentException("The arc output should be an instance of " . $expectedInterface);
            }
        }

        $this->output = $node;
    }

    protected function getExpectedInterface(NodeInterface $node)
    {
        if ($node instanceof TransactionInterface) {
            return 'Vespolina\Workflow\PlaceInterface';
        }

        return 'Vespolina\Workflow\TransactionInterface';
    }
}
