<?php

namespace Vespolina\Tests\Workflow;

use Vespolina\Tests\WorkflowCommon;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    public function testData()
    {
        $token = WorkflowCommon::createToken();
        $this->assertNull($token->getData('missing'), 'non-existent data should return null');
        $reaction = 'Yeah, bitch! Magnets! Oh!';
        $token->setData('laptop', $reaction);
        $this->assertSame($reaction, $token->getData('laptop'), 'the set data should be returned');
    }
}