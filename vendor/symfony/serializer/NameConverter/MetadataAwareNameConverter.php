<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\NameConverter;

use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * @author Fabien Bourigault <bourigaultfabien@gmail.com>
 */
final class MetadataAwareNameConverter implements AdvancedNameConverterInterface
{
    private $metadataFactory;

    /**
     * @var NameConverterInterface|AdvancedNameConverterInterface|null
     */
    private $fallbackNameConverter;

    private static $normalizeCache = [];

    private static $denormalizeCache = [];

    private static $attributesMetadataCache = [];

    public function __construct(ClassMetadataFactoryInterface $metadataFactory, NameConverterInterface $fallbackNameConverter = null)
    {
        $this->metadataFactory = $metadataFactory;
        $this->fallbackNameConverter = $fallbackNameConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($propertyName, string $class = null, string $format = null, array $context = [])
    {
        if (null === $class) {
            return $this->normalizeFallback($propertyName, $class, $format, $context);
        }

        if (!isset(self::$normalizeCache[$class][$propertyName])) {
            self::$normalizeCache[$class][$propertyName] = $this->getCacheValueForNormalization($propertyName, $class);
        }

        return self::$normalizeCache[$class][$propertyName] ?? $this->normalizeFallback($propertyName, $class, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($propertyName, string $class = null, string $format = null, array $context = [])
    {
        if (null === $class) {
            return $this->denormalizeFallback($propertyName, $class, $format, $context);
        }

        $cacheKey = $this->getCacheKey($class, $context);
        if (!isset(self::$denormalizeCache[$cacheKey][$propertyName])) {
            self::$denormalizeCache[$cacheKey][$propertyName] = $this->getCacheValueForDenormalization($propertyName, $class, $context);
        }

        return self::$denormalizeCache[$cacheKey][$propertyName] ?? $this->denormalizeFallback($propertyName, $class, $format, $context);
    }

    private function getCacheValueForNormalization($propertyName, string $class)
    {
        if (!$this->metadataFactory->hasMetadataFor($class)) {
            return null;
        }

        $attributesMetadata = $this->metadataFactory->getMetadataFor($class)->getAttributesMetadata();
        if (!isset($attributesMetadata[$propertyName])) {
            return null;
        }

        return $attributesMetadata[$propertyName]->getSerializedName() ?? null;
    }

    private function normalizeFallback($propertyName, string $class = null, string $format = null, array $context = [])
    {
        return $this->fallbackNameConverter ? $this->fallbackNameConverter->normalize($propertyName, $class, $format, $context) : $propertyName;
    }

    private function getCacheValueForDenormalization($propertyName, string $class, array $context)
    {
        $cacheKey = $this->getCacheKey($class, $context);
        if (!isset(self::$attributesMetadataCache[$cacheKey])) {
            self::$attributesMetadataCache[$cacheKey] = $this->getCacheValueForAttributesMetadata($class, $context);
        }

        return self::$attributesMetadataCache[$cacheKey][$propertyName] ?? null;
    }

    private function denormalizeFallback($propertyName, string $class = null, string $format = null, array $context = [])
    {
        return $this->fallbackNameConverter ? $this->fallbackNameConverter->denormalize($propertyName, $class, $format, $context) : $propertyName;
    }

    private function getCacheValueForAttributesMetadata(string $class, array $context): array
    {
        if (!$this->metadataFactory->hasMetadataFor($class)) {
            return [];
        }

        $classMetadata = $this->metadataFactory->getMetadataFor($class);

        $cache = [];
        foreach ($classMetadata->getAttributesMetadata() as $name => $metadata) {
            if (null === $metadata->getSerializedName()) {
                continue;
            }

            $groups = $metadata->getGroups();
            if (!$groups && ($context[AbstractNormalizer::GROUPS] ?? [])) {
                continue;
            }
            if ($groups && !array_intersect($groups, $context[AbstractNormalizer::GROUPS] ?? [])) {
                continue;
            }

            $cache[$metadata->getSerializedName()] = $name;
        }

        return $cache;
    }

    private function getCacheKey(string $class, array $context): string
    {
        if (isset($context['cache_key'])) {
            return $class.'-'.$context['cache_key'];
        }

        return $class.md5(serialize($context[AbstractNormalizer::GROUPS] ?? []));
    }
}
