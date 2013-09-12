<?php

namespace spec\Vespolina\Workflow\Task;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AutomaticSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Task\Automatic');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\TransactionInterface');
    }
}
