<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Workflow;

interface ArcInterface extends NodeInterface
{
    /**
     * @param TokenableInterface $tokenable
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function setFrom(TokenableInterface $tokenable);

    /**
     * @param TokenableInterface $tokenable
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function setTo(TokenableInterface $tokenable);

    /**
     * @param TokenInterface $token
     * @return boolean
     */
    public function accept(TokenInterface $token);
}
