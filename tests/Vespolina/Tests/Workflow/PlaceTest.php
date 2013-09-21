<?php

namespace Vespolina\Tests\Workflow;

use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;

class PlaceTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $logger = new Logger('test');
        $workflow = WorkflowCommon::createWorkflow($logger);
        $place = WorkflowCommon::createPlace($workflow, $logger);
        $token = WorkflowCommon::createToken();
        $workflow->addToken($token);
        $inArc = WorkflowCommon::createArc();
        $inArc->accept($token);
        $inArc->setTo($place);
        $outArc = WorkflowCommon::createArc();
        $outArc->setFrom($place);

        $this->assertTrue($place->execute($token));

        foreach ($place->getOutputs() as $arc) { // there is only one output
            $this->assertSame($token, $arc->forfeit(), 'the exact token should have been passed with one output');
        }
        $this->assertCount(1, $workflow->getTokens());

        // all tokens have been forfeited, begin next test
        $outArc2 = WorkflowCommon::createArc();
        $outArc2->setFrom($place);
        $inArc->accept($token);

        // workflow original token removed, cloned tokens replaced
        $this->assertTrue($place->execute($token));

        $outputTokens = [];
        foreach ($place->getOutputs() as $arc) { // there is only one output
            $arcToken = $arc->forfeit();
            $outputTokens[] = $arcToken;
            $this->assertEquals($token, $arcToken, 'the exact token should have been passed with one output');
            $this->assertNotSame($token, $arcToken, 'the exact token should have been passed with one output');
        }

        $this->assertCount(2, $workflow->getTokens());
        foreach ($workflow->getTokens() as $workflowToken) {
            $this->assertNotSame($token, $workflowToken);
            $this->assertContains($workflowToken, $outputTokens);
        }
    }
}