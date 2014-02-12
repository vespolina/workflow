<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests;

use Monolog\Logger;
use Vespolina\Workflow\Arc;
use Vespolina\Workflow\Place;
use Vespolina\Workflow\Token;
use Vespolina\Workflow\Transaction;
use Vespolina\Workflow\Workflow;

class WorkflowCommon
{
    public static function createArc()
    {
        return new Arc();
    }

    public static function createPlace($workflow = null, $logger = null)
    {
        $place = new Place();
        if ($workflow) {
            $place->setWorkflow($workflow, $logger);
        }

        return $place;
    }

    public static function createTransaction()
    {
        return new Transaction();
    }

    public static function createToken()
    {
        return new Token();
    }

    public static function createWorkflow($logger = null, $queueHandler = null)
    {
        if (!$logger) {
            $logger = new Logger('test');
        }

        return new Workflow($logger, $queueHandler);
    }
} 