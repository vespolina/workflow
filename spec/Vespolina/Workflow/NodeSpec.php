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
     * @param \Vespolina\Workflow\Token $token
     */
    function it_should_accept_a_token($token)
    {
        $this->accept($token)->shouldReturn(true);
        $this->getTokens()->shouldHaveToken($token);
    }

    public function getMatchers()
    {
        return [
            'haveToken' => function($tokens, $token) {
                    return in_array($token, $tokens);
            },
        ];
    }
}
