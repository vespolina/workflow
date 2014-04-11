<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Workflow\Task;

use Vespolina\Workflow\TokenInterface;
use Vespolina\Workflow\Transaction;

abstract class Queue extends Transaction
{
    /**
     * {@inheritdoc}
     */
    public function accept(TokenInterface $token)
    {
        $this->tokens[] = $token;

        $success = true;
        try {
            $success = $success && $this->preExecute($token);
            $success = $success && $this->workflow->produceQueue($token);
        } catch (\Exception $e) {
            if ($e instanceof ProcessingFailureException) {
                $this->workflow->addError($e->getMessage());
            }

            return false;
        }

        return $success;
    }

    public function consume(TokenInterface $token)
    {
        $this->tokens[] = $token;

        $success = true;
        try {
            $success = $success && $this->execute($token);
            $success = $success && $this->postExecute($token);
            $success = $success && $this->cleanUp($token);
        } catch (\Exception $e) {
            if ($e instanceof ProcessingFailureException) {
                $this->workflow->addError($e->getMessage());
            }

            return false;
        }
        $this->finalize($token);

        return $success;
    }
}
