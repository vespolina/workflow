<?php

namespace Vespolina\Tests\Workflow;

use Vespolina\Tests\WorkflowCommon;

class WorkflowTest extends \PHPUnit_Framework_TestCase
{
    public function testAddArcNode()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $arc = WorkflowCommon::createArc();
        $workflow->addNode($arc);
        $this->assertContains($arc, $workflow->getArcs());
    }

    public function testRemoveToken()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $token = WorkflowCommon::createToken();
        $token2 = clone $token;

        $workflow->addToken($token);
        $workflow->addToken($token2);

        $workflow->removeToken($token2);

        $this->assertContains($token, $workflow->getTokens());
        $this->assertNotContains($token2, $workflow->getTokens());
    }

    public function testValidateWorkflow()
    {

    }
}