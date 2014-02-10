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
use Vespolina\Workflow\Arc;
use Vespolina\Workflow\Node;
use Vespolina\Workflow\TokenInterface;
use Vespolina\Workflow\Transaction;

class WorkflowTest extends \PHPUnit_Framework_TestCase
{

    public function testAddNode()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $place = WorkflowCommon::createPlace();
        $workflow->addNode($place, 'p1');
        $nodes = $workflow->getNodes();
        $this->assertSame($place, $nodes['p1']);
    }

    public function testAccept()
    {
        $logger = $this->getMock('Monolog\Logger', array('info'), array('test'));
        $logger->expects($this->exactly(2))
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

    /**
     * @expectedException     \InvalidArgumentException
     * @expectedExceptionMessage There is no node with the label no_place
     */
    public function testConnectMissingFromNode()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $workflow->connect('no_place', 'x');
    }

    /**
     * @expectedException     \InvalidArgumentException
     * @expectedExceptionMessage There is no node with the label no_transaction
     */
    public function testConnectMissingToNode()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $place = WorkflowCommon::createPlace();
        $workflow->addNode($place, 'p1');
        $workflow->connect('p1', 'no_transaction');
    }

    /**
     * @expectedException     \InvalidArgumentException
     * @expectedExceptionMessage You can only connect a Vespolina\Workflow\TransactionInterface to a Vespolina\Workflow\PlaceInterface
     */
    public function testConnectTwoPlaceNodes()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $place = WorkflowCommon::createPlace();
        $workflow->addNode($place, 'p1');
        $place2 = WorkflowCommon::createPlace();
        $workflow->addNode($place2, 'p2');
        $workflow->connect('p1', 'p2');
    }

    /**
     * @expectedException     \InvalidArgumentException
     * @expectedExceptionMessage You can only connect a Vespolina\Workflow\PlaceInterface to a Vespolina\Workflow\TransactionInterface
     */
    public function testConnectTwoTransactionPlaceNodes()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $transaction = WorkflowCommon::createTransaction();
        $workflow->addNode($transaction, 't1');
        $transaction2 = WorkflowCommon::createTransaction();
        $workflow->addNode($transaction2, 't2');
        $workflow->connect('t1', 't2');
    }

    public function testConnect()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $place = WorkflowCommon::createPlace();
        $workflow->addNode($place, 'p1');
        $transaction = WorkflowCommon::createTransaction();
        $workflow->addNode($transaction, 't1');
        $workflow->connect('p1', 't1');
        $arcs = $workflow->getArcs();
        $this->assertCount(1, $arcs, 'there should only be one arc');
        $arc = array_shift($arcs);
        $this->assertSame('p1', $arc->from, 'the place should be set as the from in the arc');
        $this->assertSame('t1', $arc->to, 'the transaction should be set as the to in the arc');
        $placeOutputs = $place->getOutputs();
        $this->assertCount(1, $placeOutputs, 'the place should only have one output');
        $this->assertSame($arc, array_shift($placeOutputs), 'the output should be the new arc');
        $transactionInputs = $transaction->getInputs();
        $this->assertCount(1, $transactionInputs, 'the transaction should only have one input');
        $this->assertSame($arc, array_shift($transactionInputs), 'the input should be the new arc');
    }

    public function testConnectThroughPlace()
    {
        $trans1 = WorkflowCommon::createTransaction();
        $trans1->setName('trans1');
        $trans2 = WorkflowCommon::createTransaction();
        $trans2->setName('trans2');
        $workflow = WorkflowCommon::createWorkflow();

        $place = $workflow->connectThroughPlace($trans1, $trans2);
        $this->assertInstanceOf('Vespolina\Workflow\PlaceInterface', $place, 'a place should have been created and returned');
        $nodes = $workflow->getNodes();
        $this->assertTrue(in_array($place, $nodes), 'the place should have been added to the nodes');
        $this->assertTrue(in_array($trans1, $nodes), 'the from should have been added to the nodes');
        $this->assertTrue(in_array($trans2, $nodes), 'the to should have been added to the nodes');
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

    public function testFinalizeSingleArc()
    {
        $logger = new Logger('test');
        $workflow = WorkflowCommon::createWorkflow($logger);
        $tokenable = new ExtendedTransaction();
        $tokenable->setWorkflow($workflow, $logger);
        $token = WorkflowCommon::createToken();
        $tokenable->addToken($token);
        $workflow->addToken($token);
        $outArc = new Arc();
        $outArc->setFrom($tokenable);
        $outArc->setTo(WorkflowCommon::createPlace());

        $this->assertTrue($workflow->finalize($tokenable, $token));
        $this->assertCount(1, $workflow->getTokens());
        $this->assertNotContains($token, $tokenable->getTokens(), 'the token should not longer be in tokenable');
    }

    public function testFinalizeMultipleArcs()
    {
        $logger = new Logger('test');
        $workflow = WorkflowCommon::createWorkflow($logger);
        $tokenable = new ExtendedTransaction();
        $tokenable->setWorkflow($workflow, $logger);
        $token = WorkflowCommon::createToken();
        $tokenable->addToken($token);
        $workflow->addToken($token);

        $place1 = WorkflowCommon::createPlace();
        $place1->setWorkflow($workflow, $logger);
        $arc1 = $this->getMock('Vespolina\Workflow\Arc', array('getExpectedInterface'));
        $arc1->expects($this->any())
            ->method('getExpectedInterface')
            ->will($this->returnValue('Vespolina\Workflow\PlaceInterface'));
        $arc1->setFrom($tokenable);
        $arc1->setTo($place1);

        $place2 = WorkflowCommon::createPlace();
        $place2->setWorkflow($workflow, $logger);
        $arc2 = $this->getMock('Vespolina\Workflow\Arc', array('getExpectedInterface'));
        $arc2->expects($this->any())
            ->method('getExpectedInterface')
            ->will($this->returnValue('Vespolina\Workflow\PlaceInterface'));
        $arc2->setFrom($tokenable);
        $arc2->setTo($place2);

        $this->assertTrue($workflow->finalize($tokenable, $token));

        $outputTokens = array_merge($place1->getTokens(), $place2->getTokens());
        foreach ($outputTokens as $curToken) {
            $this->assertNotSame($token, $curToken, 'the token should be a clone');
        }

        $this->assertNotContains($token, $tokenable->getTokens(), 'the token should not longer be in tokenable');
        $this->assertCount(2, $workflow->getTokens());
        foreach ($workflow->getTokens() as $workflowToken) {
            $this->assertNotSame($token, $workflowToken, 'the tokens should be clones');
            $this->assertContains($workflowToken, $outputTokens, 'the workflow tokens should also be the same token in the arc "to"');
        }
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

class ExtendedTransaction extends Transaction
{
    public function addToken(TokenInterface $token)
    {
        $this->tokens[] = $token;
    }
}