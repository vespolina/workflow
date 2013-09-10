<?php

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TokenSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Token');
    }
}
