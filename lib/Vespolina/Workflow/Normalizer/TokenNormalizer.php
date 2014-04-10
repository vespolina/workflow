<?php

namespace Vespolina\Workflow\Normalizer;

use Normalt\Normalizer\AggregateNormalizer;
use Normalt\Normalizer\AggregateNormalizerAware;
use Vespolina\Workflow\Token;

class TokenNormalizer extends AggregateNormalizer implements AggregateNormalizerAware
{
    public function normalize($object, $format = null, array $context = array())
    {
        $data = [];
        foreach ($object->getData() as $key => $datum) {
 //           $data[$key] = $this->normalizeValues($object->getData());
        }
        return array(
            'data'          => $this->normalizeValues($object->getData()),
            'location'      => $object->getLocation(),
        );
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
       return new Token($this->denormalizeValues($data['data']), $data['location']);
    }

    public function setAggregateNormalizer(AggregateNormalizer $aggregate)
    {
        $this->aggregate = $aggregate;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
       return $type === 'Vespolina\Workflow\Token';
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Token;
    }

    private function normalizeValue($data)
    {
        switch (true) {
            case is_scalar($data):
                return $data;

            case is_array($data):
                return $this->normalizeValues($data);

            default:
                return $this->aggregate->normalize($data);
        }
    }

    private function normalizeValues($data)
    {
        $normalized = array();

        foreach ($data as $key => $value) {
            $normalized[$key] = $this->normalizeValue($value);
        }

        return $normalized;
    }

    private function denormalizeValue($data)
    {
        switch (true) {
            case is_scalar($data):
                return $data;

            case $this->aggregate->supportsDenormalization($data, 'array'):
                return $this->aggregate->denormalize($data, 'array');

            case is_array($data):
                return $this->denormalizeValues($data);
        }
    }

    private function denormalizeValues($data)
    {
        $denormalized = array();

        foreach ($data as $key => $value) {
            $denormalized[$key] = $this->denormalizeValue($value);
        }

        return $denormalized;
    }
}
