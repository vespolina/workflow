<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Workflow;

class Place extends Node implements PlaceInterface
{
    /**
     * This is a default execution action, move the token to the outputs.
     *
     * {@inheritdoc}
     */
    public function execute(TokenInterface $token)
    {
        try {
            return $this->finalize($token);
        } catch (\Exception $e) {
            return false;
        }
    }
}
