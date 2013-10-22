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
        $workflow->connect($workflow->getStart(), $a);
        $p = WorkflowCommon::createPlace($workflow, $logger);
        $workflow->connect($a, $p);
        $b = new AutoB();
        $workflow->connect($p, $b);
        $workflow->connect($b, $workflow->getFinish());

        $token = WorkflowCommon::createToken();

        $workflow->accept($token);

        $expected = array(
            'Token accepted into workflow',
            'Token accepted into workflow.start',
            'Token accepted into Vespolina\Tests\Functional\AutoA',
            'Token accepted into Vespolina\Workflow\Place',
            'Token accepted into Vespolina\Tests\Functional\AutoB',
            'Token accepted into workflow.finish',
        );
        foreach ($expected as $logEntry) {
            $this->assertTrue($handler->hasInfo($logEntry));
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