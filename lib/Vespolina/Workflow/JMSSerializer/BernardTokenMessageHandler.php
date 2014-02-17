<?php

namespace Vespolina\Workflow\JMSSerializer;

use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use Vespolina\Workflow\Message\BernardTokenMessage;

class BernardTokenMessageHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribingMethods()
    {
        return array(array(
            'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
            'format'    => 'json',
            'type'      => 'Vespolina\Workflow\Message\BernardTokenMessage',
            'method'    => 'serializeMessage',
        ), array(
            'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
            'format'    => 'json',
            'type'      => 'Vespolina\Workflow\Message\BernardTokenMessage',
            'method'    => 'deserializeMessage',
        ));
    }

    /**
     * @param  AbstractVisitor      $visitor
     * @param  BernardTokenMessage  $message
     * @param  string               $type
     * @param  Context              $context
     * @return array
     */
    public function serializeMessage(AbstractVisitor $visitor, BernardTokenMessage $message, $type, Context $context)
    {
        $token = $message->getToken();
        $tokenClass = get_class($token);
        $type = array(
            'name' => $tokenClass,
            'params' => array(),
        );

        $data = array(
            'args'      => $context->accept($token, $type),
            'class'     => bernard_encode_class_name($tokenClass),
            'name'      => $message->getName(),
            'queue'     => $message->getQueue(),
        );

        $visitor->setRoot($data);

        return $data;
    }

    /**
     * @param  AbstractVisitor $visitor
     * @param  array           $data
     * @param  string          $type
     * @param  Context         $context
     * @return Envelope
     */
    public function deserializeMessage(AbstractVisitor $visitor, array $data, $type, Context $context)
    {
        $data['class'] = bernard_decode_class_string($data['class']);
        $type = [
            'name' => $data['class'],
            'params' => null,
        ];
        $message = new BernardTokenMessage($data['name'], $context->accept($data['args'], $type));

        return $message;
    }

    /**
     * @param  AbstractVisitor $visitor
     * @param  DefaultMessage  $message
     * @param  string          $type
     * @param  Context         $context
     * @return array
     */
    public function serializeDefaultMessage(AbstractVisitor $visitor, DefaultMessage $message, $type, Context $context)
    {
        return array('name' => $message->getName()) + get_object_vars($message);
    }

    /**
     * @param  AbstractVisitor $visitor
     * @param  array           $data
     * @param  string          $type
     * @param  Context         $context
     * @return Envelope
     */
    public function deserializeDefaultMessage(AbstractVisitor $visitor, array $data, $type, Context $context)
    {
        return new DefaultMessage($data['name'], $data);
    }
}
