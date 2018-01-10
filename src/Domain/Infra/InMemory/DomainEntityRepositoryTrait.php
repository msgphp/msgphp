<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\InMemory;

use MsgPhp\Domain\{AbstractDomainEntityRepositoryTrait, DomainCollectionInterface, DomainIdentityMapInterface, DomainIdInterface};
use MsgPhp\Domain\Exception\{DuplicateEntityException, EntityNotFoundException};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait DomainEntityRepositoryTrait
{
    use AbstractDomainEntityRepositoryTrait;

    private $memory;
    private $accessor;

    public function __construct(string $class, DomainIdentityMapInterface $identityMap, GlobalObjectMemory $memory = null, ObjectFieldAccessor $accessor = null)
    {
        $this->class = $class;
        $this->identityMap = $identityMap;
        $this->memory = $memory ?? GlobalObjectMemory::createDefault();
        $this->accessor = $accessor ?? new ObjectFieldAccessor();
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
        $identity = $this->toIdentity(...$ids = func_get_args());

        if (null === $identity) {
            throw EntityNotFoundException::createForId($this->class, ...$ids);
        }

        return $this->doFindByFields($identity);
    }

    private function doFindByFields(array $fields)
    {
        if ($entity = $this->doFindAllByFields($fields)->first()) {
            return $entity;
        }

        if ($this->isIdentity($fields)) {
            throw EntityNotFoundException::createForId($this->class, ...array_values($fields));
        }

        throw EntityNotFoundException::createForFields($this->class, $fields);
    }

    private function doExists($id, ...$idN): bool
    {
        $identity = $this->toIdentity(...func_get_args());

        if (null === $identity) {
            return false;
        }

        return $this->doExistsByFields($identity);
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
        if ($this->memory->contains($entity)) {
            return;
        }

        // @todo generate in memory ids from sequence

        if ($this->doExists(...$ids = array_values($this->identityMap->getIdentity($entity)))) {
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

    /**
     * @param object $entity
     */
    private function matchesFields($entity, array $fields): bool
    {
        foreach ($fields as $field => $value) {
            $value = $this->normalizeIdentifier($value);
            $knownValue = $this->normalizeIdentifier($this->accessor->getValue($entity, $field));
            if ($knownValue instanceof DomainIdInterface) {
                $knownValue = $knownValue->isEmpty() ? null : $knownValue->toString();
            }
            if ($value instanceof DomainIdInterface) {
                $value = $value->isEmpty() ? null : $value->toString();
            }

            // @todo match entity value against domain id/primitive value and vice versa

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
}
