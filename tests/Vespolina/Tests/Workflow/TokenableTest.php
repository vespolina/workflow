<?php

namespace Vespolina\Tests\Workflow;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;
use Vespolina\Workflow\Tokenable;
use Vespolina\Workflow\TokenInterface;

class TokenableTest extends \PHPUnit_Framework_TestCase
{
    public function testAccept()
    {
        $handler = new TestHandler();
        $logger = new Logger('test', array($handler));
        $workflow = WorkflowCommon::createWorkflow($logger);
        $token = WorkflowCommon::createToken();
        $tokenable = $this->getMock('Vespolina\Workflow\Tokenable',
            array('preExecute', 'execute', 'postExecute', 'cleanUp')
        );
        $tokenable->setWorkflow($workflow, $logger);
        $inArc = WorkflowCommon::createArc();
        $inArc->accept($token);
        $inArc->setTo($tokenable);
        $otherArc = WorkflowCommon::createArc();
        $otherArc->accept(WorkflowCommon::createToken());
        $otherArc->setTo($tokenable);

        $tokenable->expects($this->once())
            ->method('preExecute');
        $tokenable->expects($this->once())
            ->method('execute');
        $tokenable->expects($this->once())
            ->method('postExecute');
        $tokenable->expects($this->once())
            ->method('cleanUp');

        $this->assertTrue($tokenable->accept($token));
        $this->assertContains($token, $tokenable->getTokens());
        $this->assertTrue($handler->hasInfo('Token accepted into '));

        foreach ($tokenable->getInputs() as $arc) {
            $this->assertNotSame($token, $arc->forfeit(), 'the token should not be in the input now');
        }
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

    public function testTransferTokenToOutputs()
    {
        $logger = new Logger('test');
        $workflow = WorkflowCommon::createWorkflow($logger);
        $tokenable = new ExtendedTokenable();
        $tokenable->setWorkflow($workflow, $logger);
        $token = WorkflowCommon::createToken();
        $tokenable->setToken($token);
        $workflow->addToken($token);
        $outArc = WorkflowCommon::createArc();
        $outArc->setFrom($tokenable);

        $tokenable->finalize($token);
        foreach ($tokenable->getOutputs() as $arc) { // there is only one output
            $this->assertSame($token, $arc->forfeit(), 'the exact token should have been passed with one output');
        }
        $this->assertCount(1, $workflow->getTokens());
        $this->assertNotContains($token, $tokenable->getTokens(), 'the token should not longer be in tokenable');

        // all tokens have been forfeited, begin next test
        $outArc2 = WorkflowCommon::createArc();
        $outArc2->setFrom($tokenable);
        $tokenable->setToken($token);

        // workflow original token removed, cloned tokens replaced
        $tokenable->finalize($token);

        $outputTokens = [];
        foreach ($tokenable->getOutputs() as $arc) {
            $arcToken = $arc->forfeit();
            $outputTokens[] = $arcToken;
            $this->assertEquals($token, $arcToken, 'the exact token should have been passed with one output');
            $this->assertNotSame($token, $arcToken, 'the exact token should have been passed with one output');
        }

        $this->assertNotContains($token, $tokenable->getTokens(), 'the token should not longer be in tokenable');
        $this->assertCount(2, $workflow->getTokens());
        foreach ($workflow->getTokens() as $workflowToken) {
            $this->assertNotSame($token, $workflowToken);
            $this->assertContains($workflowToken, $outputTokens);
        }
    }
}

class ExtendedTokenable extends Tokenable
{
    public function setToken(TokenInterface $token)
    {
        $this->tokens[] = $token;
    }

    public function finalize(TokenInterface $token)
    {
        return parent::finalize($token);
    }
}