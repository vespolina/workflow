<?php

namespace Vespolina\Workflow\Task;

use Vespolina\Workflow\TokenInterface;
use Vespolina\Workflow\Transaction;

class Automatic extends Transaction
{
    protected function cleanUp(TokenInterface $token)
    {
        $this->finalize($token);
    }
}
