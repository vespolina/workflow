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
use Monolog\Logger;

$logger = new Logger('test');
$workflow = new Workflow($logger);

// create sequence
$a = new Automatic();
$b = new Automatic();
$p = new Place();

$workflow->connect($workflow->getStart(), $a);
$workflow->connect($a, $p);
$workflow->connect($p, $b);
$workflow->connect($b, $workflow->getFinish());

$workflow->accept(new Token());