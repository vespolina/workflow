<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Workflow;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;
use Vespolina\Workflow\Node;
use Vespolina\Workflow\Exception\ProcessingFailureException;
use Vespolina\Workflow\TokenInterface;

class NodeTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $node = new TestNode();
        $this->assertSame('Vespolina\Tests\Workflow\TestNode', $node->getName(), 'a missing name should return the class name');

        $node->setName('test node');
        $this->assertSame('test node', $node->getName(), 'the set name should be returned');
    }

    public function testSetWorkflow()
    {
        $logger = new Logger('test');
        $workflow = WorkflowCommon::createWorkflow($logger);
        $node = new TestNode();
        $this->assertSame($node, $node->setWorkflow($workflow, $logger));
    }

    public function testAccept()
    {
        $handler = new TestHandler();
        $logger = new Logger('test', array($handler));
        $workflow = WorkflowCommon::createWorkflow($logger);
        $token = WorkflowCommon::createToken();
        $tokenable = $this->getMock('Vespolina\Workflow\Node',
            array('preExecute', 'execute', 'postExecute', 'cleanUp')
        );
        $tokenable->setWorkflow($workflow, $logger);
        $inArc = WorkflowCommon::createArc();
        $inArc->setTo($tokenable);
        $otherArc = WorkflowCommon::createArc();
        $otherArc->setTo($tokenable);

        $tokenable->expects($this->once())
            ->method('preExecute')
            ->will($this->returnValue(true));
        $tokenable->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(true));
        $tokenable->expects($this->once())
            ->method('postExecute')
            ->will($this->returnValue(true));
        $tokenable->expects($this->once())
            ->method('cleanUp')
            ->will($this->returnValue(true));

        $this->assertTrue($tokenable->accept($token));
        $this->assertContains($token, $tokenable->getTokens());
        $expected = 'Token accepted into ' . get_class($tokenable);
        $this->assertTrue($handler->hasInfo($expected));

        $this->assertSame($tokenable, $token->getLocation(), 'the location of the token should be updated');
    }

    public function testProcessExceptionHandling()
    {
        $handler = new TestHandler();
        $logger = new Logger('test', array($handler));
        $workflow = WorkflowCommon::createWorkflow($logger);
        $token = WorkflowCommon::createToken();
        $tokenable = $this->getMock('Vespolina\Workflow\Node',
            array('preExecute')
        );
        $tokenable->setWorkflow($workflow, $logger);
        $inArc = WorkflowCommon::createArc();
        $inArc->setTo($tokenable);
        $otherArc = WorkflowCommon::createArc();
        $otherArc->setTo($tokenable);

        $tokenable->expects($this->once())
            ->method('preExecute')
            ->will($this->throwException(new ProcessingFailureException('testing')))
        ;
        $this->assertFalse($tokenable->accept($token), 'a failure should return false');
        $this->assertContains('testing', $workflow->getErrors(), 'the error message from the failure should be in workflow');
    }

    public function testAddInput()
    {
        $tokenable = $this->getMockForAbstractClass('Vespolina\Workflow\Node');
        $arc = WorkflowCommon::createArc();
        $tokenable->addInput($arc);
        $this->assertContains($arc, $tokenable->getInputs());
    }

    public function testAddOutput()
    {
        $tokenable = $this->getMockForAbstractClass('Vespolina\Workflow\Node');
        $arc = WorkflowCommon::createArc();
        $tokenable->addOutput($arc);
        $this->assertContains($arc, $tokenable->getOutputs());
    }

    public function testFinalizeSingleArc()
    {
        $logger = new Logger('test');
        $workflow = WorkflowCommon::createWorkflow($logger);
        $tokenable = new ExtendedTokenable();
        $tokenable->setWorkflow($workflow, $logger);
        $token = WorkflowCommon::createToken();
        $tokenable->setToken($token);
        $workflow->addToken($token);
        $outArc = $this->getMock('Vespolina\Workflow\Arc', array('accept'));
        $outArc->expects($this->once())
            ->method('accept')
            ->will($this->returnValue(true));
        $outArc->setFrom($tokenable);

        $this->assertTrue($tokenable->finalize($token));
        $this->assertCount(1, $workflow->getTokens());
        $this->assertNotContains($token, $tokenable->getTokens(), 'the token should not longer be in tokenable');
    }

    public function testFinalizeMultipleArcs()
    {
        $logger = new Logger('test');
        $workflow = WorkflowCommon::createWorkflow($logger);
        $tokenable = new ExtendedTokenable();
        $tokenable->setWorkflow($workflow, $logger);
        $token = WorkflowCommon::createToken();
        $tokenable->setToken($token);
        $workflow->addToken($token);

        $place1 = WorkflowCommon::createPlace();
        $place1->setWorkflow($workflow, $logger);
        $arc1 = $this->getMock('Vespolina\Workflow\Arc', array('getExpectedInterface'));
        $arc1->expects($this->any())
            ->method('getExpectedInterface')
            ->will($this->returnValue('Vespolina\Workflow\PlaceInterface'));
        $arc1->setFrom($tokenable);
        $arc1->setTo($place1);

        $place2 = WorkflowCommon::createPlace();
        $place2->setWorkflow($workflow, $logger);
        $arc2 = $this->getMock('Vespolina\Workflow\Arc', array('getExpectedInterface'));
        $arc2->expects($this->any())
            ->method('getExpectedInterface')
            ->will($this->returnValue('Vespolina\Workflow\PlaceInterface'));
        $arc2->setFrom($tokenable);
        $arc2->setTo($place2);

        $this->assertTrue($tokenable->finalize($token));

        $outputTokens = array_merge($place1->getTokens(), $place2->getTokens());
        foreach ($outputTokens as $curToken) {
            $this->assertNotSame($token, $curToken, 'the token should be a clone');
        }

        $this->assertNotContains($token, $tokenable->getTokens(), 'the token should not longer be in tokenable');
        $this->assertCount(2, $workflow->getTokens());
        foreach ($workflow->getTokens() as $workflowToken) {
            $this->assertNotSame($token, $workflowToken, 'the tokens should be clones');
            $this->assertContains($workflowToken, $outputTokens, 'the workflow tokens should also be the same token in the arc "to"');
        }
    }
}

class TestNode extends Node
{

}

class ExtendedTokenable extends Node
{
    public function setToken(TokenInterface $token)
    {
        $this->tokens[] = $token;
    }

    public function finalize(TokenInterface $token)
    {
        return parent::finalize($token);
    }
}