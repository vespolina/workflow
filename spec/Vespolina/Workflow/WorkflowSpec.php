<?php

namespace spec\Vespolina\Workflow;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Vespolina\Workflow\PlaceInterface;

class WorkflowSpec extends ObjectBehavior
{
    /**
     * @param \Monolog\Logger $logger
     * @param \Monolog\Handler\TestHandler $handler
     */
    function let($logger, $handler)
    {
        $logger->pushHandler($handler);
        $this->beConstructedWith($logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Vespolina\Workflow\Workflow');
        $this->getNodes()->shouldHaveCount(2);
        $this->getNodes()->shouldBePlaces();
        $this->getNodes()->shouldContainName('workflow.input');
        $this->getNodes()->shouldContainName('workflow.output');
    }

    /**
     * @param \Vespolina\Workflow\Token $token
     */
    function it_should_accept_a_token_to_start($token)
    {
        $this->accept($token)->shouldReturn(true);
        $this->getTokens()->shouldContainToken($token);
        $this->getInput()->getTokens()->shouldContainToken($token);
    }

    /**
     * @param \Vespolina\Workflow\Place $from
     * @param \Vespolina\Workflow\Task\Automatic $to
     */
    function it_should_create_an_arc($from, $to)
    {
        $this->createArc($from, $to)->shouldReturnAnInstanceOf('Vespolina\Workflow\Arc');
    }

    /**
     * @param \Vespolina\Workflow\Place $place
     * @param \Vespolina\Workflow\Task\Automatic $transaction
     * @param \Vespolina\Workflow\Place $place2
     */
    function it_should_connect_nodes($place, $transaction, $place2)
    {
        $this->shouldThrow(new \InvalidArgumentException('You can only connect a Vespolina\Workflow\TransactionInterface to a Vespolina\Workflow\PlaceInterface'))->duringConnect($place, $place);
        $this->connect($place, $transaction)->shouldReturnAnInstanceOf('Vespolina\Workflow\Arc');
        $this->getNodes()->shouldContainNode($place);
        $this->getNodes()->shouldContainNode($transaction);
        $this->connect($transaction, $place2)->shouldPassConnectRules($this->getNodes());

    }

    /**
     * @param \Monolog\Logger $logger
     * @param \Monolog\Handler\TestHandler $handler
     * @param \Vespolina\Workflow\Task\Automatic $a
     * @param \Vespolina\Workflow\Place $c2
     * @param \Vespolina\Workflow\Task\Automatic $b
     *
     */
    function it_should_handle_sequence_pattern($logger, $handler, $a, $c1, $b)
    {
        $logger->pushHandler($handler);


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
            'containToken' => function($tokens, $token) {
                return in_array($token, $tokens);
            },
            'passConnectRules' => function($arc, $nodes) {
                $a = 0;
                if (!in_array($arc, $nodes)) {
                    return false;
                }
            }
        ];
    }
}
