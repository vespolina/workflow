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
     * @param \Vespolina\Workflow\PlaceInterface $place
     * @param \Vespolina\Workflow\TransactionInterface $transaction
     */
    function it_should_only_connect_between_a_place_and_transaction($place, $transaction)
    {
        $this->setFrom($place);
        $this->shouldThrow(new \InvalidArgumentException('The "to" node should be an instance of Vespolina\Workflow\TransactionInterface'))->duringSetTo($place);

        $this->setTo($transaction);
        $this->shouldThrow(new \InvalidArgumentException('The "from" node should be an instance of Vespolina\Workflow\PlaceInterface'))->duringSetFrom($transaction);
    }

    /**
     * @param \Vespolina\Workflow\Place $place
     * @param \Vespolina\Workflow\Transaction $transaction
     */
    function it_should_add_itself_to_the_target_node($place, $transaction)
    {
        $this->setFrom($place)->shouldSetSelfInOutput($this, $place);
        $this->setTo($transaction)->shouldSetSelfInInput($this, $transaction);

    }

    public function getMatchers()
    {
        return [
            'setSelfInOutput' => function($return, $arc, $tokenable) {
                return in_array($arc, $tokenable->getOutputs());
            },
            'setSelfInInput' => function($return, $arc, $tokenable) {
                return in_array($arc, $tokenable->getInputs());
            },
        ];
    }
}
