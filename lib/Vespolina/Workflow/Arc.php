<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Workflow;

class Arc
{
    public $from;
    public $to;

    public function setFrom($from)
    {
        $this->from = $from;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setTo(array $to)
    {
        $this->to = $to;
    }

    public function getTo()
    {
        return $this->to;
    }
}
