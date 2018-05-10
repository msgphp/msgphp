<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine\Event;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ObjectFieldMappingListener
{
    private $mappings;

    /** @var ClassMetadataFactory|null */
    private $metadataFactory;

    public function __construct(array $mappings)
    {
        $this->mappings = $mappings;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        if (!$this->mappings) {
            return;
        }

        $this->metadataFactory = $event->getEntityManager()->getMetadataFactory();

        $this->processClassFields($event->getClassMetadata());

        $this->metadataFactory = null;
    }

    private function processClassFields(ClassMetadataInfo $metadata, \ReflectionClass $class = null): void
    {
        $class = $class ?? $metadata->getReflectionClass();

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

            $info = ['fieldName' => $field] + $info;

            if (isset($info['type']) && method_exists($metadata, $method = 'map'.ucfirst($info['type']))) {
                unset($info['type']);
                $metadata->$method($info);

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

        if ($parent->idGenerator) {
            $class->setIdGenerator($parent->idGenerator);
        }
    }

    private function getMetadata(string $class): ClassMetadataInfo
    {
        if (null === $this->metadataFactory) {
            throw new \LogicException('Metadata factory not set.');
        }

        $loaded = $this->metadataFactory->getLoadedMetadata();

        /** @var ClassMetadataInfo $metadata */
        $metadata = $loaded[$class] ?? $this->metadataFactory->getMetadataFor($class);

        return $metadata;
    }
}
