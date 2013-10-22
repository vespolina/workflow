<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Workflow;

use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;
use Vespolina\Workflow\Node;

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
}

class TestNode extends Node
{

}