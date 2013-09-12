<?php

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TokenableSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Tokenable');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\Node');
    }

    /**
     * @param \Vespolina\Workflow\Token $token
     */
    function it_should_accept_a_token($token)
    {
        $this->accept($token)->shouldReturn(true);
        $this->getTokens()->shouldHaveToken($token);
    }

    /**
     * @param \Vespolina\Workflow\Arc $arc
     */
    function it_should_add_a_single_input_arc($arc)
    {
        $this->addInput($arc);
        $this->getInputs()->shouldHaveArc($arc);
    }

    /**
     * @param \Vespolina\Workflow\Arc $arc
     */
    function it_should_add_a_single_output_arc($arc)
    {
        $this->addOutput($arc);
        $this->getOutputs()->shouldHaveArc($arc);
    }

    public function getMatchers()
    {
        return [
            'haveToken' => function($tokens, $token) {
                    return in_array($token, $tokens);
            },
            'haveArc' => function($arcs, $arc) {
                    return in_array($arc, $arcs);
            },
        ];
    }
}
