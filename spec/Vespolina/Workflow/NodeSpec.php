<?php

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NodeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Node');
    }

    function it_should_have_a_name()
    {
        $this->setName('name');
        $this->getName()->shouldBeEqualTo('name');
    }

    /**
     * @param \Vespolina\Workflow\Workflow $workflow
     * @param \Monolog\Logger $logger
     */
    function it_should_set_the_workflow_and_logger($workflow, $logger)
    {
        $this->setWorkflow($workflow, $logger)->shouldReturn($this);
    }
}
