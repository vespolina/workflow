<?php

require __DIR__ . '/../vendor/autoload.php';

use Vespolina\Workflow\Dumper\GraphvizDumper;
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

$dumper = new GraphvizDumper($workflow);
$dumper->dump();