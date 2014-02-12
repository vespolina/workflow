<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Functional;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Vespolina\Tests\WorkflowCommon;
use Vespolina\Workflow\Task\Automatic;
use Vespolina\Workflow\TokenInterface;

class WorkflowTest extends \PHPUnit_Framework_TestCase
{
    /**
     *  O -> [A] -> O -> [B] -> O
     * in          p1          out
     */
    public function testSequentialPattern()
    {
        $handler = new TestHandler();
        $logger = new Logger('test', array($handler));
        $workflow = WorkflowCommon::createWorkflow($logger);

        // create sequence
        $a = new AutoA();
        $workflow->addNode($a, 'a');
        $place = WorkflowCommon::createPlace();
        $workflow->addNode($place, 'p1');
        $b = new AutoB();
        $workflow->addNode($b, 'b');

        $workflow->connectToStart('a');
        $workflow->connect('a', 'p1');
        $workflow->connect('p1', 'b');
        $workflow->connectToFinish('b');

        $token = WorkflowCommon::createToken();

        $workflow->accept($token);

        $expected = array(
            'Token accepted into workflow',
            'Token advanced into workflow.start',
            'Token advanced into a',
            'Token advanced into p1',
            'Token advanced into b',
            'Token advanced into workflow.finish',
        );
        foreach ($expected as $logEntry) {
            $this->assertTrue($handler->hasInfo($logEntry), "failed log entry $logEntry");
        }
    }
}

class AutoA extends Automatic
{
    public function execute(TokenInterface $token)
    {
        if ($token->getData('autoB')) {
            return false;
        }
        $token->setData('autoA', true);

        return true;
    }
}

class AutoB extends Automatic
{
    public function execute(TokenInterface $token)
    {
        if (!$token->getData('autoA')) {
            return false;
        }
        $token->setData('autoB', true);

        return true;
    }
}