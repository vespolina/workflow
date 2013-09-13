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
    function it_is_initializable($workflow, $logger)
    {
        $this->shouldHaveType('Vespolina\Workflow\Tokenable');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\Tokenable');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\Node');

        $this->setWorkflow($workflow, $logger);
    }
}
