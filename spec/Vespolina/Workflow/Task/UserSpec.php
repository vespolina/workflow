<?php

namespace spec\Vespolina\Workflow\Task;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Task\User');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\TransactionInterface');
    }
}
