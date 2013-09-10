<?php

namespace Vespolina\Workflow;

class Node implements NodeInterface
{
    protected $name;
    protected $tokens;

    public function accept(TokenInterface $token)
    {
        $this->tokens[] = $token;

        return true;
    }

    /**
     * Set the name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Return the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the tokens
     *
     * @return array of \Vespolina\Workflow\TokenInterface
     */
    public function getTokens()
    {
        return $this->tokens;
    }
}
