<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\InMemory;

use MsgPhp\Domain\{DomainCollectionInterface, DomainIdInterface};
use MsgPhp\Domain\Exception\{DuplicateEntityException, EntityNotFoundException};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait DomainEntityRepositoryTrait
{
    private $class;
    private $memory;

    // @todo in memory id sequence generator

    public function __construct(string $class, GlobalObjectMemory $memory = null)
    {
        $this->class = $class;
        $this->memory = $memory ?? GlobalObjectMemory::createDefault();
    }

    private function doFindAll(int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        return $this->createResultSet($this->memory->all($this->class), $offset, $limit);
    }

    private function doFindAllByFields(array $fields, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        if (!$fields) {
            throw new \LogicException('No fields provided.');
        }

        $i = -1;
        $entities = [];
        foreach ($this->memory->all($this->class) as $entity) {
            if (!$this->matchesFields($entity, $fields) || ++$i < $offset) {
                continue;
            }

            if ($limit && $i >= ($offset + $limit)) {
                break;
            }

            $entities[] = $entity;
        }

        return $this->createResultSet($entities);
    }

    private function doFind($id, ...$idN)
    {
        if (!$this->isValidEntityId($ids = func_get_args())) {
            throw EntityNotFoundException::createForId($this->class, ...$ids);
        }

        return $this->doFindByFields(array_combine($this->idFields, $ids));
    }

    private function doFindByFields(array $fields)
    {
        if ($entity = $this->doFindAllByFields($fields)->first()) {
            return $entity;
        }

        if (count($fields) === count($this->idFields) && [] === array_diff(array_keys($fields), $this->idFields)) {
            throw EntityNotFoundException::createForId($this->class, ...array_values($fields));
        }

        throw EntityNotFoundException::createForFields($this->class, $fields);
    }

    private function doExists($id, ...$idN): bool
    {
        if (!$this->isValidEntityId($ids = func_get_args())) {
            return false;
        }

        return $this->doExistsByFields(array_combine($this->idFields, $ids));
    }

    private function doExistsByFields(array $fields): bool
    {
        try {
            $this->doFindByFields($fields);

            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    /**
     * @param object $entity
     */
    private function doSave($entity): void
    {
        if (!$this->memory->contains($entity) && $this->doExists(...$ids = $this->getEntityId($entity))) {
            throw DuplicateEntityException::createForId(get_class($entity), ...$ids);
        }

        $this->memory->persist($entity);
    }

    /**
     * @param object $entity
     */
    private function doDelete($entity): void
    {
        $this->memory->remove($entity);
    }

    private function createResultSet(iterable $entities, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        if ($entities instanceof \Traversable) {
            $entities = iterator_to_array($entities);
        }

        if ($offset || $limit) {
            $entities = array_slice($entities, $offset, $limit ?: null);
        }

        return new DomainCollection($entities);
    }

    private function isValidEntityId(array $ids): bool
    {
        if (count($ids) !== count($this->idFields)) {
            return false;
        }

        foreach ($ids as $id) {
            if (null === $this->normalizeEntityId($id)) {
                return false;
            }
        }

        return true;
    }

    private function normalizeEntityId($id)
    {
        if ($id instanceof DomainIdInterface) {
            return $id->isEmpty() ? null : $id;
        }

        if (is_object($id)) {
            // @todo currently unsupported
            return $id;
        }

        if (is_array($id)) {
            return array_map(function ($id) {
                return $this->normalizeEntityId($id);
            }, $id);
        }

        return $id;
    }

    /**
     * @param object $entity
     */
    private function matchesFields($entity, array $fields): bool
    {
        foreach ($fields as $field => $value) {
            $value = $this->normalizeEntityId($value);
            $knownValue = $this->normalizeEntityId(self::getEntityField($entity, $field));
            if ($knownValue instanceof DomainIdInterface) {
                $knownValue = $knownValue->isEmpty() ? null : $knownValue->toString();
            }
            if ($value instanceof DomainIdInterface) {
                $value = $value->isEmpty() ? null : $value->toString();
            }

            if ($value === $knownValue) {
                continue;
            }

            if (null === $value xor null === $knownValue) {
                return false;
            }

            if ((string) $value === (string) $knownValue) {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * @param object $entity
     */
    private function getEntityId($entity): array
    {
        $id = [];

        foreach ($this->idFields as $field) {
            $id[] = self::getEntityField($entity, $field);
        }

        return $id;
    }

    /**
     * @param object $entity
     */
    private static function getEntityField($entity, string $field)
    {
        if (method_exists($entity, $method = 'get'.ucfirst($field))) {
            return $entity->$method();
        }

        if (method_exists($entity, $field)) {
            return $entity->$field();
        }

        if (property_exists($entity, $field)) {
            return $entity->$field;
        }

        throw new \UnexpectedValueException(sprintf('Unknown field name "%s" for entity "%s"', $field, get_class($entity)));
    }
}
