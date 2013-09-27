<?php

namespace Vespolina\Tests\Workflow;

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
}

class TestNode extends Node
{

}