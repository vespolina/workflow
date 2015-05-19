<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require __DIR__ . '/../vendor/autoload.php';

use Vespolina\Workflow\Task\Automatic;
use Vespolina\Workflow\Place;
use Vespolina\Workflow\Workflow;
use Vespolina\Workflow\Token;
use Vespolina\Workflow\TokenInterface;
use Monolog\Logger;

class NodeA extends Automatic
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

class NodeB extends Automatic
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

$logger = new Logger('test');
$workflow = new Workflow($logger);

// create sequence
$a = new NodeA();
$b = new NodeB();
$place = new Place();
$place->setWorkflow($workflow, $logger);

$workflow->addNode($a, 'a');
$workflow->addNode($place, 'p1');
$workflow->addNode($b, 'b');

$workflow->connectToStart('a');
$workflow->connect('a', 'p1');
$workflow->connect('p1', 'b');
$workflow->connectToFinish('b');

$workflow->accept(new Token());

