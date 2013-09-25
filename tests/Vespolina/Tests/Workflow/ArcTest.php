<?php

namespace Vespolina\Tests\Workflow;

use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;

class ArcTest extends \PHPUnit_Framework_TestCase
{
    public function testAccept()
    {
        $arc = WorkflowCommon::createArc();
        $tokenable = $this->getMock('Vespolina\Workflow\Tokenable', array('accept'));
        $tokenable->expects($this->once())
            ->method('accept')
            ->will($this->returnValue(true));
        $arc->setTo($tokenable);
        $token = WorkflowCommon::createToken();
        $arc->accept($token);

        $this->assertSame($tokenable, $token->getLocation(), 'the tokenable should be the location of the token');
    }
}