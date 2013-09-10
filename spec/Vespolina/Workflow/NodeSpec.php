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
}
