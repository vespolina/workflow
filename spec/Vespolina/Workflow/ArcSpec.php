<?php

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ArcSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Arc');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\Node');
    }

    /**
     * @param \Vespolina\Workflow\Place $place
     * @param \Vespolina\Workflow\Transaction $transaction
     */
    function it_should_only_connect_between_a_place_and_transaction($place, $transaction)
    {
        $this->setFrom($place);
        $this->shouldThrow(new \InvalidArgumentException('The "to" node should be an instance of Vespolina\Workflow\TransactionInterface'))->duringSetTo($place);

        $this->setTo($transaction);
        $this->shouldThrow(new \InvalidArgumentException('The "from" node should be an instance of Vespolina\Workflow\PlaceInterface'))->duringSetFrom($transaction);
    }

    /**
     * @param \Vespolina\Workflow\Tokenable $from
     */
    function it_should_add_self_to_tokenable_from($from)
    {
        $this->setFrom($from);
        $from->addOutput($this)->shouldHaveBeenCalled();
    }

    /**
     * @param \Vespolina\Workflow\Tokenable $to
     */
    function it_should_add_self_to_tokenable_to($to)
    {
        $this->setTo($to);
        $to->addInput($this)->shouldHaveBeenCalled();
    }

    /**
     * @param \Vespolina\Workflow\Token $token
     */
    function it_should_accept_and_forfeit_a_token($token)
    {
        $this->forfeit()->shouldReturn(null);
        $this->accept($token)->shouldReturn(true);
        $this->shouldThrow(new \InvalidArgumentException('There is already a token in this arc'))->duringAccept($token);
        $this->forfeit()->shouldReturn($token);
        $this->forfeit()->shouldReturn(null);
    }

    /**
     * @param \Vespolina\Workflow\Token $token
     * @param \Vespolina\Workflow\Token $failingToken
     */
    function it_should_compare_its_token_to_another($token, $failingToken)
    {
        $this->hasToken($token)->shouldReturn(false);
        $this->accept($token);
        $this->hasToken($token)->shouldReturn(true);
        $this->hasToken($failingToken)->shouldReturn(false);
    }
}
