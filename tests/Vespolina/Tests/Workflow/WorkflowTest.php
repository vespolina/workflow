<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Workflow;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;

class WorkflowTest extends \PHPUnit_Framework_TestCase
{
    public function testAccept()
    {
        $logger = $this->getMock('Monolog\Logger', array('info'), array('test'));
        $logger->expects($this->once())
            ->method('info');
        $workflow = WorkflowCommon::createWorkflow($logger);
        $input = $this->getMock('Vespolina\Workflow\Place', array('accept'));
        $input->expects($this->once())
            ->method('accept')
            ->will($this->returnValue(true));
        $rp = new \ReflectionProperty($workflow, 'start');
        $rp->setAccessible(true);
        $rp->setValue($workflow, $input);
        $token = WorkflowCommon::createToken();
        $workflow->accept($token);
        $this->assertContains($token, $workflow->getTokens(), '');
    }

    public function testErrors()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $workflow->addError('error 1');
        $workflow->addError('error 2');
        $errors = $workflow->getErrors();
        $this->assertContains('error 1', $errors);
        $this->assertContains('error 2', $errors);
    }

    public function testAddArcNode()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $arc = WorkflowCommon::createArc();
        $workflow->addArc($arc);
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

    public function testCreateToken()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $this->assertInstanceof('Vespolina\Workflow\TokenInterface', $workflow->createToken(), 'an instance of TokenInterface should be create');

        $string = 'string';
        $object = new \stdClass();
        $data = [
            'string' => $string,
            'object' => $object,
        ];
        $token = $workflow->createToken($data);
        $this->assertSame($string, $token->getData('string'), 'the string should be returned');
        $this->assertSame($object, $token->getData('object'), 'the object should be returned');
    }

    public function testValidateWorkflowValidOutputs()
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);
        $workflow = WorkflowCommon::createWorkflow($logger);
        $this->assertFalse($workflow->validateWorkflow(), 'this should fail');
        $this->assertTrue($handler->hasDebug('Missing output arc for workflow.start'));
    }

    public function testValidateWorkflowConnectedArcs()
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);
        $workflow = WorkflowCommon::createWorkflow($logger);
        $arc = WorkflowCommon::createArc();
        $arc->setName('a1');
        $arc->setFrom($workflow->getStart());

        $this->assertFalse($workflow->validateWorkflow(), 'this should fail');
        $this->assertTrue($handler->hasDebug('Broken arc a1 from workflow.start'));
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
        $a1->setFrom($workflow->getStart());
        $a1->setTo($transaction);
        $a2 = WorkflowCommon::createArc();
        $a2->setName('a2');
        $a2->setFrom($transaction);
        $a2->setTo($workflow->getFinish());

        $this->assertTrue($workflow->validateWorkflow());

        $expected = array(
            'Node workflow.start reached, step 1',
            'Traversing arc a1, step 2',
            'Node transaction reached, step 3',
            'Traversing arc a2, step 4',
            'Node workflow.finish reached, step 5',
        );
        foreach ($expected as $logEntry) {
            $this->assertTrue($handler->hasInfo($logEntry));
        }
    }
}