<?php

namespace Vespolina\Workflow;

class Place extends Tokenable implements PlaceInterface
{
    /**
     * This is a default execution action, move the token to the outputs.
     *
     * {@inheritdoc}
     */
    public function execute(TokenInterface $token)
    {
        try {
            $this->finalize($token);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
