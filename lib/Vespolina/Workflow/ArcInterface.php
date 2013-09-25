<?php

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
