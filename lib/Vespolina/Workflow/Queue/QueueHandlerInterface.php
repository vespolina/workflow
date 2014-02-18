<?php

namespace Vespolina\Workflow\Queue;

interface QueueHandlerInterface 
{
    public function enqueue($location, $token);
} 