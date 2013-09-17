<?php

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

require_once __DIR__ . '/TokenableBehavior.php';

class PlaceSpec extends TokenableBehavior
{
    /**
     * @param \Vespolina\Workflow\Workflow $workflow
     * @param \Monolog\Logger $logger
     */
    function let($workflow, $logger)
    {
        $this->setWorkflow($workflow, $logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Tokenable');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\Tokenable');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\Node');
    }

    /**
     * @param \Vespolina\Workflow\Token $token
     * @param \Vespolina\Workflow\Token $failingToken
     */
    function it_should_move_a_token_from_input_to_outputs_when_executed($token, $failingToken)
    {
        $this->addInput($token);
        $this->execute($token)->shouldReturn(true);
        $this->getInputs()->shouldNotContainToken($token);
        $this->getOutputs()->shouldEachContainToken($token);

    }
}
