<?php

namespace spec\Vespolina\Workflow\Task;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TimeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Task\Time');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\TransactionInterface');
    }
}
