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
        $this->assertSame(
            'Vespolina\Tests\Workflow\TestNode',
            $node->getName(),
            'a missing name should return the class name'
        );

        $node->setName('test node');
        $this->assertSame('test node', $node->getName(), 'the set name should be returned');
    }

    public function testSetWorkflow()
    {
        $handler = new TestHandler();
        $logger = new Logger('test', array($handler));
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
        $tokenable = $this->getMock(
            'Vespolina\Workflow\Node',
            array('preExecute', 'execute', 'postExecute', 'cleanUp', 'finalize')
        );

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
        $tokenable->expects($this->once())
            ->method('finalize')
            ->will($this->returnValue(true));

        $this->assertTrue($tokenable->accept($token));
        $this->assertContains($token, $tokenable->getTokens());
    }

    public function testProcessExceptionHandling()
    {
        $handler = new TestHandler();
        $logger = new Logger('test', array($handler));
        $workflow = WorkflowCommon::createWorkflow($logger);
        $token = WorkflowCommon::createToken();
        $tokenable = $this->getMock(
            'Vespolina\Workflow\Node',
            array('preExecute')
        );
        $tokenable->setWorkflow($workflow, $logger);

        $tokenable->expects($this->once())
            ->method('preExecute')
            ->will($this->throwException(new ProcessingFailureException('testing')))
        ;
        $this->assertFalse($tokenable->accept($token), 'a failure should return false');
        $this->assertContains(
            'testing',
            $workflow->getErrors(),
            'the error message from the failure should be in workflow'
        );
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
}

class TestNode extends Node
{

}