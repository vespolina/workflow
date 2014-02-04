<?php

namespace Vespolina\Workflow\Message;

use Bernard\Message\AbstractMessage;
use Vespolina\Workflow\Token;

class BernardTokenMessage extends AbstractMessage
{
    protected $name;
    protected $token;

    public function __construct($name, Token $token)
    {
        $this->token = $token;
        $this->name = preg_replace('/(^([0-9]+))|([^[:alnum:]-_+])/i', '', $name);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getToken()
    {
        return $this->token;
    }
} 