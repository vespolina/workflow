<?php

namespace Vespolina\Tests\Workflow;

use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;

class ArcTest extends \PHPUnit_Framework_TestCase
{
    public function testAccept()
    {
        $arc = WorkflowCommon::createArc();
        $token = WorkflowCommon::createToken();
        $arc->accept($token);

        $this->assertSame($arc, $token->getLocation(), 'the arc should be the location of the token');
    }
}