<?php

namespace spec\Vespolina\Workflow\Task;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MessageSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Task\Message');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\TransactionInterface');
    }
}
