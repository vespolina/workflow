<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;
use Vespolina\Workflow\Node;
use Vespolina\Workflow\Place;
use Vespolina\Workflow\PlaceInterface;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Vespolina\Workflow\Task\Automatic;

class WorkflowSpec extends ObjectBehavior
{
    function let(Logger $logger, TestHandler $handler)
    {
        $logger->pushHandler($handler);
        $this->beConstructedWith($logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Workflow');
        $this->getNodes()->shouldHaveCount(2);
        $this->getNodes()->shouldBePlaces();
        $this->getNodes()->shouldContainName('workflow.start');
        $this->getNodes()->shouldContainName('workflow.finish');
    }

    public function getMatchers()
    {
        return [
            'bePlaces' => function($nodes) {
                foreach ($nodes as $node) {
                    if (!$node instanceof PlaceInterface) {
                        return false;
                    }
                }

                return true;
            },
            'containName' => function($nodes, $name) {
                foreach ($nodes as $node) {
                    if ($node->getName() == $name) {
                        return true;
                    }
                }

                return false;
            },
            'containNode' => function($nodes, $node) {
                foreach ($nodes as $curNode) {
                    if ($curNode == $node) {
                        return true;
                    }
                }

                return false;
            },
            'containNodeOnce' => function($nodes, $node) {
                $hits = 0;
                foreach ($nodes as $curNode) {
                    if ($curNode == $node) {
                        $hits++;
                    }
                }

                return $hits == 1 ? true : false;
            },
            'containToken' => function($tokens, $token) {
                return in_array($token, $tokens);
            },
        ];
    }
}
