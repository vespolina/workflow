<?php

namespace Vespolina\Tests\Workflow;

use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;

class TokenableTest extends \PHPUnit_Framework_TestCase
{
    public function testAccept()
    {
        $logger = new Logger('test');
        $workflow = WorkflowCommon::createWorkflow($logger);
        $token = WorkflowCommon::createToken();

        $tokenable = $this->getMock('Vespolina\Workflow\Tokenable',
            array('preExecute', 'execute', 'postExecute')
        );
        $tokenable->setWorkflow($workflow, $logger);
        $tokenable->expects($this->once())
            ->method('preExecute');
        $tokenable->expects($this->once())
            ->method('execute');
        $tokenable->expects($this->once())
            ->method('postExecute');

        $this->assertTrue($tokenable->accept($token));
        $this->assertContains($token, $tokenable->getTokens());
    }

    public function testAddInput()
    {
        $tokenable = $this->getMockForAbstractClass('Vespolina\Workflow\Tokenable');
        $arc = WorkflowCommon::createArc();
        $tokenable->addInput($arc);
        $this->assertContains($arc, $tokenable->getInputs());
    }

    public function testAddOutput()
    {
        $tokenable = $this->getMockForAbstractClass('Vespolina\Workflow\Tokenable');
        $arc = WorkflowCommon::createArc();
        $tokenable->addOutput($arc);
        $this->assertContains($arc, $tokenable->getOutputs());
    }
}