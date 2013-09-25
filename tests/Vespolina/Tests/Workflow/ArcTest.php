<?php

namespace Vespolina\Tests\Workflow;

use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;

class ArcTest extends \PHPUnit_Framework_TestCase
{
    public function testAcceptSuccess()
    {
        $arc = WorkflowCommon::createArc();
        $tokenable = $this->getMock('Vespolina\Workflow\Tokenable', array('accept'));
        $tokenable->expects($this->once())
            ->method('accept')
            ->will($this->returnValue(true));
        $arc->setTo($tokenable);
        $token = WorkflowCommon::createToken();
        $this->assertTrue($arc->accept($token), 'true should be returned when successful');
    }

    public function testAcceptFailure()
    {
        $arc = WorkflowCommon::createArc();
        $tokenable = $this->getMock('Vespolina\Workflow\Tokenable', array('accept'));
        $tokenable->expects($this->once())
            ->method('accept')
            ->will($this->throwException(new \Exception));
        $arc->setTo($tokenable);
        $token = WorkflowCommon::createToken();
        $this->assertFalse($arc->accept($token), 'true should be returned when successful');
    }
}