<?php

namespace Vespolina\Workflow\Normalizer;

use Normalt\Normalizer\AggregateNormalizer;
use Normalt\Normalizer\AggregateNormalizerAware;

class TypeNormalizer extends AggregateNormalizer implements AggregateNormalizerAware
{
    public function normalize($data, $format = null, array $context = array())
    {
        return $this->normalizeValues($data);
    }

    public function denormalize($data, $type, $format = null, array $context = array())
    {
        return $this->denormalizeValues($data);
    }

    public function setAggregateNormalizer(AggregateNormalizer $aggregate)
    {
        $this->aggregate = $aggregate;
    }

    public function supportsNormalization($data, $format = null)
    {
        return is_scalar($data) || is_array($data);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return (is_scalar($data) || is_array($data)) && !class_exists($type);
    }

    private function normalizeValue($data)
    {
        switch (true) {
            case is_scalar($data):
                return $data;

            case is_array($data):
                return $this->normalizeValues($data);

            case $normalizer = $this->getNormalizer($data):
                return $normalizer->normalize($data);

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

            case $normalizer = $this->getDenormalizer($data, 'array'):
                return $normalizer->denormalize($data, 'array');

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


    private function createPrototype($class)
    {
        return unserialize(sprintf('O:%u:"%s":0:{}', strlen($class), $class));
    }
}
