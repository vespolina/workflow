<?php

namespace Vespolina\Workflow;

class Place extends Tokenable implements PlaceInterface
{
    /**
     * This is a default execution action, move the token to the outputs.
     *
     * @param TokenInterface $token
     * @return boolean
     */
    public function execute(TokenInterface $token)
    {
        $this->finalize($token);

        return true;
    }
}
