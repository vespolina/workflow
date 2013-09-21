<?php

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Vespolina\Workflow\Arc;

class PlaceSpec
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
}
