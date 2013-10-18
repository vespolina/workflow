<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;
use Vespolina\Workflow\TokenableInterface;

class ArcSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Arc');
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

    function it_should_add_self_to_tokenable_from(TokenableInterface $from)
    {
        $this->setFrom($from);
        $from->addOutput($this)->shouldHaveBeenCalled();
    }

    function it_should_add_self_to_tokenable_to(TokenableInterface $to)
    {
        $this->setTo($to);
        $to->addInput($this)->shouldHaveBeenCalled();
    }
}
