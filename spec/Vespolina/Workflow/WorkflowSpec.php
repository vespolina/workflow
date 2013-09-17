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
     * @param \Vespolina\Workflow\Node $node
     */
    function it_should_add_a_node($node, $logger)
    {
        $this->addNode($node);
        $this->getNodes()->shouldContainNode($node);
        $node->setWorkflow($this, $logger)->shouldHaveBeenCalled();

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
        $this->connect($transaction, $place2);
        $this->getNodes()->shouldContainNodeOnce($transaction);
        $this->getNodes()->shouldContainNode($place2);
    }

    /**
     * @param \Vespolina\Workflow\Token $token
     */
    function it_should_log_accept_token($logger, $token)
    {
        $this->accept($token);
        $logger->info('Token accepted into workflow', array('token' => $token))->shouldHaveBeenCalled();
    }

    /**
     * @param \Vespolina\Workflow\Task\Automatic $a
     * @param \Vespolina\Workflow\Place $c2
     * @param \Vespolina\Workflow\Task\Automatic $b
     *
     */
    function it_should_handle_sequence_pattern($a, $c1, $b)
    {


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
