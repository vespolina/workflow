<?php

namespace Vespolina\Tests\Functional;

use Vespolina\Tests\WorkflowCommon;

class WorkflowTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param \Vespolina\Workflow\Task\Automatic $a
     * @param \Vespolina\Workflow\Place $c2
     * @param \Vespolina\Workflow\Task\Automatic $b
     *
     */
    function it_should_handle_sequence_pattern($a, $c1, $b)
    {


    }
    public function testSequencePattern()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $token = WorkflowCommon::createToken();

        $this->assertTrue($workflow->accept($token));
        $this->assertContains($token, $workflow->getTokens());

        $this->getTokens()->shouldContainToken($token);
        $this->getInput()->getTokens()->shouldContainToken($token);
    }

    /**
     * @param \Vespolina\Workflow\Token $token
     */
    function it_should_log_accept_token($logger, $token)
    {
        $this->accept($token);
        $logger->info('Token accepted into workflow', array('token' => $token))->shouldHaveBeenCalled();
    }
}