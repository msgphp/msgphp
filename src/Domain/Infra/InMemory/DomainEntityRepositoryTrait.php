<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\InMemory;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Exception\DuplicateEntityException;
use MsgPhp\Domain\Exception\EntityNotFoundException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait DomainEntityRepositoryTrait
{
    private $class;
    private $memory;

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
        $entities = [];
        foreach ($this->memory->all($this->class) as $entity) {
            // @todo duplicated in doFindByFields
            foreach ($fields as $field => $value) {
                $knownValue = $this->getEntityField($entity, $field);
                if ($knownValue instanceof DomainIdInterface && $value instanceof DomainIdInterface && $knownValue->equals($value)) {
                    continue;
                }

                if ($value !== $knownValue) {
                    continue 2;
                }
            }

            $entities[] = $entity;
        }

        return $this->createResultSet($entities, $offset, $limit);
    }

    private function doFind($id, ...$idN)
    {
        return $this->doFindByFields(array_combine($this->idFields, func_get_args()));
    }

    private function doFindByFields(array $fields)
    {
        foreach ($this->memory->all($this->class) as $entity) {
            foreach ($fields as $field => $value) {
                $knownValue = $this->getEntityField($entity, $field);
                if ($knownValue instanceof DomainIdInterface && $value instanceof DomainIdInterface && $knownValue->equals($value)) {
                    continue;
                } elseif ($knownValue instanceof DomainIdInterface) {
                    //$knownValue = $knownValue->toString();
                } elseif ($value instanceof DomainIdInterface) {
                    //$value = $value->toString();
                }

                if ($value !== $knownValue) {
                    continue 2;
                }
            }

            return $entity;
        }

        throw EntityNotFoundException::createForFields($this->class, $fields);
    }

    private function doExists($id, ...$idN): bool
    {
        return $this->doExistsByFields(array_combine($this->idFields, func_get_args()));
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
        if (!$this->memory->contains($entity) && $this->doExists(...$id = $this->getEntityId($entity))) {
            throw DuplicateEntityException::createForId(get_class($entity), $id);
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

    /**
     * @param object $entity
     */
    private function getEntityId($entity): array
    {
        $id = [];

        foreach ($this->idFields as $field) {
            $id[] = $this->getEntityField($entity, $field);
        }

        return $id;
    }

    /**
     * @param object $entity
     */
    private function getEntityField($entity, string $field)
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
