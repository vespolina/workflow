<?php

namespace Vespolina\Workflow\Normalizer;

use Bernard\Normalizer\AbstractAggregateNormalizerAware;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Vespolina\Workflow\Message\BernardTokenMessage;
use Vespolina\Workflow\Token;

class TokenMessageNormalizer extends AbstractAggregateNormalizerAware implements NormalizerInterface, DenormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'class'     => get_class($object->getToken()),
            'name'      => $object->getName(),
            'token'     => $this->aggregate->normalize($object->getToken()),
        );
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return new BernardTokenMessage($data['name'], $this->aggregate->denormalize($data['token'], $data['class']));
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'Vespolina\Workflow\Message\BernardTokenMessage';
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof BernardTokenMessage;
    }
}
