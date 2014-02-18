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

    public function testConstruct()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $nodes = $workflow->getNodes();
        $this->assertInstanceOf('Vespolina\Workflow\Place', $nodes['workflow.start'], 'a place with the location workflow.start should have been created');
        $this->assertInstanceOf('Vespolina\Workflow\Place', $nodes['workflow.finish'], 'a place with the location workflow.finish should have been created');
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
        $workflow->addNode($input, 'workflow.start');
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
        $this->assertCount(1, $placeOutputs, 'the place should only have  one output');
        $this->assertSame($arc, array_shift($placeOutputs), 'the output should be the new arc');
        $transactionInputs = $transaction->getInputs();
        $this->assertCount(1, $transactionInputs, 'the transaction should only have one input');
        $this->assertSame($arc, array_shift($transactionInputs), 'the input should be the new arc');
    }

    public function testConnectToStart()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $transaction = WorkflowCommon::createTransaction();
        $workflow->addNode($transaction, 't1');
        $workflow->connectToStart('t1');
        $arcs = $workflow->getArcs();
        $this->assertCount(1, $arcs, 'there should only be one arc');
        $arc = array_shift($arcs);
        $this->assertSame('workflow.start', $arc->from, 'workflow.start should be set as the from in the arc');
        $this->assertSame('t1', $arc->to, 'the transaction should be set as the to in the arc');
        $placeOutputs = $workflow->getNodes()['workflow.start']->getOutputs();
        $this->assertCount(1, $placeOutputs, 'the place should only have one output');
        $this->assertSame($arc, array_shift($placeOutputs), 'the output should be the new arc');
        $transactionInputs = $transaction->getInputs();
        $this->assertCount(1, $transactionInputs, 'the transaction should only have one input');
        $this->assertSame($arc, array_shift($transactionInputs), 'the input should be the new arc');
    }

    public function testConnectToFinish()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $transaction = WorkflowCommon::createTransaction();
        $workflow->addNode($transaction, 't1');
        $workflow->connectToFinish('t1');
        $arcs = $workflow->getArcs();
        $this->assertCount(1, $arcs, 'there should only be one arc');
        $arc = array_shift($arcs);
        $this->assertSame('workflow.finish', $arc->to, 'workflow.start should be set as the from in the arc');
        $this->assertSame('t1', $arc->from, 'the transaction should be set as the to in the arc');
        $placeInputs = $workflow->getNodes()['workflow.finish']->getInputs();
        $this->assertCount(1, $placeInputs, 'the place should only have one input');
        $this->assertSame($arc, array_shift($placeInputs), 'the input should be the new arc');
        $transactionOutputs = $transaction->getOutputs();
        $this->assertCount(1, $transactionOutputs, 'the transaction should only have one output');
        $this->assertSame($arc, array_shift($transactionOutputs), 'the output should be the new arc');
    }

    public function testConnectThroughPlace()
    {
        $workflow = WorkflowCommon::createWorkflow();
        $trans1 = WorkflowCommon::createTransaction();
        $workflow->addNode($trans1, 'trans1');
        $trans2 = WorkflowCommon::createTransaction();
        $workflow->addNode($trans2, 'trans2');

        $place = $workflow->connectThroughPlace('trans1', 'trans2');
        $this->assertInstanceOf('Vespolina\Workflow\PlaceInterface', $place, 'a place should have been created and returned');
        $nodes = $workflow->getNodes();
        $this->assertSame($place, $nodes['place_post_trans1'], 'the place should have been added to the nodes');
        $arcs = $workflow->getArcs();
        $this->assertCount(2, $arcs, 'there should be two arcs');

        $trans1Output = $trans1->getOutputs();
        $trans1Arc = array_shift($trans1Output);
        $this->assertContains($trans1Arc, $arcs, 'the output arc should be in the workflow arcs');
        $this->assertSame('place_post_trans1', $trans1Arc->to, 'the arc should connect to the place');
        $placeInputs = $place->getInputs();
        $this->assertSame($trans1Arc, array_shift($placeInputs), 'the place input should have the same arc ');

        $trans2Output = $trans2->getInputs();
        $trans2Arc = array_shift($trans2Output);
        $this->assertContains($trans2Arc, $arcs, 'the output arc should be in the workflow arcs');
        $this->assertSame('place_post_trans1', $trans2Arc->from, 'the arc should connect to the place');
        $placeOutputs = $place->getOutputs();
        $this->assertSame($trans2Arc, array_shift($placeOutputs), 'the place input should have the same arc ');
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
        $handler = new TestHandler();
        $logger = new Logger('test', array($handler));
        $workflow = WorkflowCommon::createWorkflow($logger);
        $tokenable = new ExtendedTransaction();
        $tokenable->setWorkflow($workflow, $logger);
        $token = WorkflowCommon::createToken();
        $workflow->addNode($tokenable, 'transaction');
        $place = WorkflowCommon::createPlace();
        $workflow->addNode($place, 'place');
        $workflow->connect('transaction', 'place');
        $tokenable->addToken($token);
        $workflow->addToken($token);

        $this->assertTrue($workflow->finalize($tokenable, $token));
        $this->assertCount(1, $workflow->getTokens());
        $this->assertNotContains($token, $tokenable->getTokens(), 'the token should not longer be in tokenable');
    }

    public function testFinalizeMultipleArcs()
    {
        $handler = new TestHandler();
        $logger = new Logger('test', array($handler));
        $workflow = WorkflowCommon::createWorkflow($logger);
        $transaction = new ExtendedTransaction();
        $workflow->addNode($transaction, 'transaction');

        $place1 = WorkflowCommon::createPlace();
        $workflow->addNode($place1, 'place1');

        $place2 = WorkflowCommon::createPlace();
        $workflow->addNode($place2, 'place2');

        $workflow->connect('transaction', 'place1');
        $workflow->connect('transaction', 'place2');
        $token = WorkflowCommon::createToken();
        $transaction->addToken($token);
        $workflow->addToken($token);

        $this->assertTrue($workflow->finalize($transaction, $token));

        $outputTokens = array_merge($place1->getTokens(), $place2->getTokens());
        foreach ($outputTokens as $curToken) {
            $this->assertNotSame($token, $curToken, 'the token should be a clone');
        }

        $this->assertNotContains($token, $transaction->getTokens(), 'the token should not longer be in tokenable');
        $this->assertCount(2, $workflow->getTokens());
        foreach ($workflow->getTokens() as $workflowToken) {
            $this->assertNotSame($token, $workflowToken, 'the tokens should be clones');
            $this->assertContains($workflowToken, $outputTokens, 'the workflow tokens should also be the same token in the arc "to"');
        }
    }

    public function testValidateWorkflowValidOutputs()
    {
        $this->markTestSkipped('validation needs to be looked at');
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);
        $workflow = WorkflowCommon::createWorkflow($logger);
        $this->assertFalse($workflow->validateWorkflow(), 'this should fail');
        $this->assertTrue($handler->hasDebug('Missing output arc for workflow.start'));
    }

    public function testValidateWorkflowConnectedArcs()
    {
        $this->markTestSkipped('validation needs to be looked at');
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
        $this->markTestSkipped('validation needs to be looked at');
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