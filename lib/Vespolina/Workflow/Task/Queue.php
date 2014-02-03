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
        $message = 'Token accepted into ' . $this->getName();
        $this->logger->info($message, array('token' => $token));
        $this->tokens[] = $token;
        $token->setLocation($this);

        $success = true;
        try {
            $success = $success && $this->preExecute($token);
            $success = $success && $this->execute($token);
        } catch (\Exception $e) {
            if ($e instanceof ProcessingFailureException) {
                $this->workflow->addError($e->getMessage());
            }

            return false;
        }

        return $success;
    }

    public function getQueueName()
    {
        throw new \Exception('The execute method needs to be implement in your class');
    }

    protected function postConsume(TokenInterface $token)
    {
        $message = 'Token continues post Consume in ' . $this->getName();
        $this->logger->info($message, array('token' => $token));

        $success = true;
        try {
            $success = $success && $this->postExecute($token);
            $success = $success && $this->cleanUp($token);
        } catch (\Exception $e) {
            if ($e instanceof ProcessingFailureException) {
                $this->workflow->addError($e->getMessage());
            }

            return false;
        }

        return $success;
    }
}
