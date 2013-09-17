<?php

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

require_once __DIR__ . '/TokenableBehavior.php';

class TransactionSpec extends TokenableBehavior
{
    /**
     * @param \Vespolina\Workflow\Workflow $workflow
     * @param \Monolog\Logger $logger
     * @param \Vespolina\Workflow\Token $token
     */
    function let($workflow, $logger, $token)
    {
        $this->setWorkflow($workflow, $logger);
        $this->execute($token)->willReturn("");
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Transaction');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\Tokenable');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\Node');
    }
}
