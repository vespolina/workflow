<?php

namespace Vespolina\Tests\Functional;

use Vespolina\Tests\WorkflowCommon;

class WorkflowTest extends \PHPUnit_Framework_TestCase
{
    /**
     *  O -> [A] -> O -> [B] -> O
     * in          p1          out
     */
    public function testSequentialPattern()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $token = WorkflowCommon::createToken();

        $a = $this->getMock('Vespolina\Workflow\Tasks\Automatic');
        $a->expects($this->once())
            ->method('accept');

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