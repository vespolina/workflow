<?php

namespace Vespolina\Tests\Workflow;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;

class WorkflowTest extends \PHPUnit_Framework_TestCase
{
    public function testAddArcNode()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $arc = WorkflowCommon::createArc();
        $workflow->addNode($arc);
        $this->assertContains($arc, $workflow->getArcs());
    }

    public function testRemoveToken()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $token = WorkflowCommon::createToken();
        $token2 = clone $token;

        $workflow->addToken($token);
        $workflow->addToken($token2);

        $workflow->removeToken($token2);

        $this->assertContains($token, $workflow->getTokens());
        $this->assertNotContains($token2, $workflow->getTokens());
    }

    public function testValidateWorkflowValidOutputs()
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);
        $workflow = WorkflowCommon::createWorkflow($logger);
        $this->assertFalse($workflow->validateWorkflow(), 'this should fail');
        $this->assertTrue($handler->hasDebug('Missing output arc for workflow.input'));
    }

    public function testValidateWorkflowConnectedArcs()
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);
        $workflow = WorkflowCommon::createWorkflow($logger);
        $arc = WorkflowCommon::createArc();
        $arc->setName('a1');
        $arc->setFrom($workflow->getInput());

        $this->assertFalse($workflow->validateWorkflow(), 'this should fail');
        $this->assertTrue($handler->hasDebug('Broken arc a1 from workflow.input'));
    }

    public function testValidateWorkflowSimple()
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $workflow = WorkflowCommon::createWorkflow($logger);
        $transaction = WorkflowCommon::createTransaction();
        $transaction->setName('transaction');
        $a1 = WorkflowCommon::createArc();
        $a1->setName('a1');
        $a1->setFrom($workflow->getInput());
        $a1->setTo($transaction);
        $a2 = WorkflowCommon::createArc();
        $a2->setName('a2');
        $a2->setFrom($transaction);
        $a2->setTo($workflow->getOutput());

        $this->assertTrue($workflow->validateWorkflow());

        $expected = array(
            'Node workflow.input reached, step 1',
            'Traversing arc a1, step 2',
            'Node transaction reached, step 3',
            'Traversing arc a2, step 4',
            'Node workflow.output reached, step 5',
        );
        foreach ($expected as $logEntry) {
            $this->assertTrue($handler->hasInfo($logEntry));
        }
    }


}