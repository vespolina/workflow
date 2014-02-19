<?php

namespace Vespolina\Workflow\JMSSerializer;

use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use Vespolina\Workflow\Token;

class TokenHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribingMethods()
    {
        return array(array(
            'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
            'format'    => 'json',
            'type'      => 'Vespolina\Workflow\Token',
            'method'    => 'serializeToken',
        ), array(
            'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
            'format'    => 'json',
            'type'      => 'Vespolina\Workflow\Token',
            'method'    => 'deserializeToken',
        ));
    }

    /**
     * @param  AbstractVisitor  $visitor
     * @param  Token            $token
     * @param  string           $type
     * @param  Context          $context
     * @return array
     */
    public function serializeToken(AbstractVisitor $visitor, Token $token, $type, Context $context)
    {
        $data = [];

        foreach ($token->getData() as $key => $datum) {
            $datumType = $this->getType($datum);
            $type = [
                'name' => $datumType,
                'params' => [],
            ];
            $argsData = '';
            if ($type['name'] === 'array') {
                $argsData = [];
                foreach ($datum as $arrayKey => $arrayData) {
                    $arrayType = [
                        'name' => $this->getType($arrayData),
                        'params' => [],
                    ];
                    $argsData[] = ['key' => $arrayKey, 'args' => $context->accept($arrayData, $arrayType), 'type' => $arrayType];
                }
            } else {
                 $argsData = $context->accept($datum, $type);
            }
            $args = ['key' => $key, 'args' => $argsData, 'type' => $type];

            $data[] = $args;
        }

        $data = array(
            'data'      => $data,
            'location'  => $token->getLocation(),
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
    public function deserializeToken(AbstractVisitor $visitor, array $data, $type, Context $context)
    {
        $token = new Token();
        $token->setLocation($data['location']);
        foreach ($data['data'] as $datum) {
            $type = $datum['type'];

            if ($type['name'] === 'array') {
                $argsData = [];
                foreach ($datum['args'] as $arrayData) {
                    $arrayKey = $arrayData['key'];
                    $argsData[$arrayKey] = $context->accept($arrayData['args'], $arrayData['type']);
                }
            } else {
                $argsData = $context->accept($datum['args'], $type);
            }
            $token->setData($datum['key'], $argsData);
        }

        return $token;
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

    public function getType($obj)
    {
        $type = gettype($obj);
        if ($type === 'object') {
            return get_class($obj);
        }

        return $type;
    }
}
