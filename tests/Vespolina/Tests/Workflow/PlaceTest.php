<?php

namespace Vespolina\Tests\Workflow;

use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;

class PlaceTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteSuccess()
    {
        $place = $this->getMock('Vespolina\Workflow\Place', array('finalize'));
        $place->expects($this->once())
            ->method('finalize')
            ->will($this->returnValue(true));
        $token = WorkflowCommon::createToken();

        $this->assertTrue($place->execute($token), 'true should be returned when successful');

    }

    public function testExecuteFailure()
    {
        $place = $this->getMock('Vespolina\Workflow\Place', array('finalize'));
        $place->expects($this->once())
            ->method('finalize')
            ->will($this->throwException(new \Exception));
        $token = WorkflowCommon::createToken();

        $this->assertFalse($place->execute($token), 'false should be returned when there is a problem');
    }
}