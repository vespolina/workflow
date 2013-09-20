<?php

namespace Vespolina\Workflow;

class Place extends Tokenable implements PlaceInterface
{
    /**
     * This is a default execution action, find the token in the input source and move it to the outputs.
     *
     * @param TokenInterface $token
     * @return boolean
     */
    public function execute(TokenInterface $token)
    {
        $inputs = $this->getInputs();
        $hasToken = null;
        foreach ($inputs as $input) {
            if ($input->hasToken($token)) {
                $hasToken = $input->forfeit();
                continue;
            }
        }
        if (!$hasToken) {
            throw new \InvalidArgumentException();
        }

        $outputs = $this->getOutputs();
        // single out, no token clone, just update the node location
        if (sizeof($outputs) == 1) {
            $output = array_shift($outputs);
            $output->accept($token);

            return true;
        }

        // multiple outs, clone for each path, remove original token
        foreach ($this->getOutputs() as $output) {
            $newToken = clone $token;
            $this->workflow->addToken($newToken);
            $output->accept($newToken);
        }
        $this->workflow->removeToken($token);

        return true;
    }
}
