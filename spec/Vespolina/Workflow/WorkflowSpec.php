<?php

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Vespolina\Workflow\PlaceInterface;

class WorkflowSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Workflow');
        $this->getNodes()->shouldHaveCount(2);
        $this->getNodes()->shouldBePlaces();
        $this->getNodes()->shouldContainName('workflow.input');
        $this->getNodes()->shouldContainName('workflow.output');
    }

    public function getMatchers()
    {
        return [
            'bePlaces' => function($nodes) {
                foreach ($nodes as $node) {
                    if (!$node instanceof PlaceInterface) {
                        return false;
                    }
                }

                return true;
            },
            'containName' => function($nodes, $name) {
                foreach ($nodes as $node) {
                    if ($node->getName() == $name) {
                        return true;
                    }
                }

                return false;
            },
        ];
    }
}
