<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Doctrine\Event;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use MsgPhp\Domain\Infrastructure\Doctrine\MappingConfig;
use MsgPhp\Domain\Infrastructure\Doctrine\ObjectMappingProvider;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ObjectMappingListener
{
    /** @var iterable<int, ObjectMappingProvider> */
    private $providers;
    private $mappingConfig;
    /** @var array<class-string, class-string> */
    private $classMapping;
    /** @var null|ClassMetadataFactory */
    private $metadataFactory;
    /** @var array<class-string, array>|null */
    private $mappings;

    /**
     * @param iterable<int, ObjectMappingProvider> $providers
     * @param array<class-string, class-string>    $classMapping
     */
    public function __construct(iterable $providers, MappingConfig $mappingConfig, array $classMapping = [])
    {
        $this->providers = $providers;
        $this->mappingConfig = $mappingConfig;
        $this->classMapping = $classMapping;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        if (null === $this->mappings) {
            $this->mappings = [];

            foreach ($this->providers as $provider) {
                foreach ($provider::provideObjectMappings($this->mappingConfig) as $object => $mapping) {
                    foreach ($mapping as $field => $fieldMapping) {
                        if (isset($fieldMapping['targetEntity'])) {
                            $mapping[$field]['targetEntity'] = $this->classMapping[$fieldMapping['targetEntity']] ?? $fieldMapping['targetEntity'];
                        }
                    }
                    $this->mappings[$object] = $mapping;
                }
            }
        }

        if (!$this->mappings) {
            return;
        }

        $this->metadataFactory = $event->getEntityManager()->getMetadataFactory();

        $this->processClassFields($event->getClassMetadata());

        $this->metadataFactory = null;
    }

    private function processClassFields(ClassMetadataInfo $metadata, ?\ReflectionClass $class = null): void
    {
        $class = $class ?? $metadata->getReflectionClass();

        /** @psalm-suppress DocblockTypeContradiction */
        if (null === $class) {
            return;
        }

        if (isset($this->mappings[$name = $class->getName()])) {
            $this->processFieldMapping($metadata, $this->mappings[$name]);
        }

        foreach ($class->getTraits() as $trait) {
            $this->processClassFields($metadata, $trait);
        }
    }

    private function processFieldMapping(ClassMetadataInfo $metadata, array $mapping): void
    {
        foreach ($mapping as $field => $info) {
            if ($metadata->hasField($field) || $metadata->hasAssociation($field)) {
                continue;
            }

            if (null !== $idGenerator = $info['id_generator'] ?? null) {
                if ($idGenerator instanceof AbstractIdGenerator) {
                    $metadata->setIdGenerator($idGenerator);
                } else {
                    $metadata->setIdGeneratorType($idGenerator);
                }
                unset($info['id_generator']);
            }

            $info = ['fieldName' => $field] + $info;

            if (isset($info['type']) && method_exists($metadata, $method = 'map'.ucfirst($info['type']))) {
                unset($info['type']);
                $metadata->{$method}($info);

                /** @see ClassMetadataFactory::doLoadMetadata() */
                if ('mapEmbedded' === $method) {
                    $embeddableMetadata = $this->getMetadata($info['class']);

                    if ($embeddableMetadata->isEmbeddedClass) {
                        $this->addNestedEmbeddedClasses($embeddableMetadata, $metadata, $field);
                    }

                    $identifier = $embeddableMetadata->getIdentifier();

                    if (!empty($identifier)) {
                        $this->inheritIdGeneratorMapping($metadata, $embeddableMetadata);
                    }

                    $metadata->inlineEmbeddable($field, $embeddableMetadata);
                }
            } else {
                $metadata->mapField($info);
            }
        }
    }

    private function addNestedEmbeddedClasses(ClassMetadataInfo $subClass, ClassMetadataInfo $parentClass, string $prefix): void
    {
        foreach ($subClass->embeddedClasses as $property => $embeddableClass) {
            if (isset($embeddableClass['inherited'])) {
                continue;
            }

            $embeddableMetadata = $this->getMetadata($embeddableClass['class']);

            $parentClass->mapEmbedded([
                'fieldName' => $prefix.'.'.$property,
                'class' => $embeddableMetadata->name,
                'columnPrefix' => $embeddableClass['columnPrefix'],
                'declaredField' => $embeddableClass['declaredField'] ? $prefix.'.'.$embeddableClass['declaredField'] : $prefix,
                'originalField' => $embeddableClass['originalField'] ?: $property,
            ]);
        }
    }

    private function inheritIdGeneratorMapping(ClassMetadataInfo $class, ClassMetadataInfo $parent): void
    {
        if ($parent->isIdGeneratorSequence()) {
            $class->setSequenceGeneratorDefinition($parent->sequenceGeneratorDefinition);
        } elseif ($parent->isIdGeneratorTable()) {
            $class->tableGeneratorDefinition = $parent->tableGeneratorDefinition;
        }

        if ($parent->generatorType) {
            $class->setIdGeneratorType($parent->generatorType);
        }

        if (!empty($parent->idGenerator)) {
            $class->setIdGenerator($parent->idGenerator);
        }
    }

    private function getMetadata(string $class): ClassMetadataInfo
    {
        if (null === $this->metadataFactory) {
            throw new \LogicException('Metadata factory not set.');
        }

        /** @var ClassMetadataInfo */
        return $this->metadataFactory->getMetadataFor($class);
    }
}
