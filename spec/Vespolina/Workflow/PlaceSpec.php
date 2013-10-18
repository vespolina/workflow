<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;

class PlaceSpec extends ObjectBehavior
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
        $this->shouldHaveType('Vespolina\Workflow\Place');
        $this->shouldReturnAnInstanceOf('Vespolina\Workflow\Node');
    }
}
