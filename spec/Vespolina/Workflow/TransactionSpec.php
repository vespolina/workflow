<?php

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TransactionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Transaction');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\Node');
    }
}
