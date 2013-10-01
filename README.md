workflow
========

[![Build Status](https://secure.travis-ci.org/vespolina/workflow.png?branch=master)](http://travis-ci.org/vespolina/workflow)
[![Total Downloads](https://poser.pugx.org/vespolina/workflow/downloads.png)](https://packagist.org/packages/vespolina/workflow)
[![Latest Stable Version](https://poser.pugx.org/vespolina/workflow/v/stable.png)](https://packagist.org/packages/vespolina/workflow)

Workflow is a library that lets you first build a graph with places and transactions
interconnecting them with arcs. And then lets you traverse the created workflow for
any number of runs each identifiable via a token. The traversing starts on an input
place node and ends in an output place node for a given workflow. Workflow uses a
logger to log the flow traversing details.

The tokenable nodes can execute custom implementations. These custom implementations
can be used to carry out flows on processes of a web application or other process
based systems.

Install
=======

```
composer require vespolina/workflow dev-master
```
Usage
=====

Suppose we want a workflow like this:
```

  O -> [A] -> O -> [B] -> O
 in          p1          out

 ```

 The code that implements and runs down on it would look like:
 ```php
 <?php

$logger = new Logger('test');
$workflow = new Workflow($logger)

// create sequence
$a = new AAutomaticTransaction();
$workflow->connect($workflow->getInput(), $a);
$p = new Place();
$p = $p->setWorkflow($workflow, $logger);
$workflow->connect($a, $p);
$b = new BAutomaticTransaction();
$workflow->connect($p, $b);
$workflow->connect($b, $workflow->getOutput());

$workflow->accept(new Token());
```

And we will see the traversing in our logs:

```cli
Token accepted into workflow
Token accepted into workflow.input
Token accepted into Vespolina\Tests\Functional\AutoA
Token accepted into Vespolina\Workflow\Place
Token accepted into Vespolina\Tests\Functional\AutoB
Token accepted into workflow.output
```