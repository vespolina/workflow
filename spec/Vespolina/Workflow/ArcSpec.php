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
        $this->setInput($place);
        $this->shouldThrow(new \InvalidArgumentException('The arc output should be an instance of Vespolina\Workflow\TransactionInterface'))->duringSetOutput($place);

        $this->setOutput($transaction);
        $this->shouldThrow(new \InvalidArgumentException('The arc output should be an instance of Vespolina\Workflow\PlaceInterface'))->duringSetInput($transaction);
    }
}
