<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine\Event;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ObjectFieldMappingListener
{
    private $typeConfig;
    private $mapping;

    public function __construct(array $typeConfig, array $mapping)
    {
        $this->typeConfig = $typeConfig;
        $this->mapping = $mapping;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        if (!$this->mapping && !$this->typeConfig) {
            return;
        }

        $metadata = $event->getClassMetadata();

        if ($this->typeConfig) {
            $this->processClassIdentifiers($metadata);
        }

        if ($this->mapping) {
            $this->processClassFields($metadata);
        }
    }

    private function processClassIdentifiers(ClassMetadataInfo $metadata): void
    {
        if ($metadata->usesIdGenerator()) {
            return;
        }

        foreach ($metadata->getIdentifierFieldNames() as $field) {
            if (!isset($this->typeConfig[$type = $metadata->getTypeOfField($field)]) || !in_array($this->typeConfig[$type]['data_type'], [Type::INTEGER, Type::BIGINT], true)) {
                continue;
            }

            $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);
        }
    }

    private function processClassFields(ClassMetadataInfo $metadata, \ReflectionClass $class = null): void
    {
        $class = $class ?? $metadata->getReflectionClass();

        foreach ($class->getTraits() as $trait) {
            $this->processClassFields($metadata, $trait);
        }

        if (isset($this->mapping[$name = $class->getName()])) {
            $this->processFieldMapping($metadata, $this->mapping[$name]);
        }
    }

    private function processFieldMapping(ClassMetadataInfo $metadata, array $fields): void
    {
        foreach ($fields as $field => $mapping) {
            if ($metadata->hasField($field) || $metadata->hasAssociation($field)) {
                continue;
            }

            $mapping = ['fieldName' => $field] + $mapping;

            if (isset($mapping['type']) && method_exists($metadata, $method = 'map'.ucfirst($mapping['type']))) {
                unset($mapping['type']);
                $metadata->$method($mapping);
            } else {
                $metadata->mapField($mapping);
            }
        }
    }
}
