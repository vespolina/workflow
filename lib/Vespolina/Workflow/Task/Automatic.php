<?php

namespace Vespolina\Workflow\Task;

use Vespolina\Workflow\Transaction;

class Automatic extends Transaction
{


    protected function cleanUp(TokenInterface $token)
    {

        $this->removeToken($token);
    }
}
