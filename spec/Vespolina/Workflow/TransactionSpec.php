<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;

class TransactionSpec extends ObjectBehavior
{
    /**
     * @param \Vespolina\Workflow\Workflow $workflow
     * @param \Monolog\Logger $logger
     * @param \Vespolina\Workflow\Token $token
     */
    function let($workflow, $logger, $token)
    {
        $this->setWorkflow($workflow, $logger, $token);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Transaction');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\Tokenable');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\Node');
    }
}
