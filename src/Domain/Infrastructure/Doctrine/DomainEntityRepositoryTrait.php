<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Doctrine;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Exception\DuplicateEntity;
use MsgPhp\Domain\Exception\EntityNotFound;
use MsgPhp\Domain\GenericDomainCollection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @template T of object
 */
trait DomainEntityRepositoryTrait
{
    /** @var class-string<T> */
    private $class;
    private $em;
    /** @var null|string */
    private $alias;

    /**
     * @param class-string<T> $class
     */
    public function __construct(string $class, EntityManagerInterface $em)
    {
        $this->class = $class;
        $this->em = $em;
    }

    private function getAlias(): string
    {
        return $this->alias ?? ($this->alias = strtolower((string) preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1_\\2', '\\1_\\2'], (string) (false === ($i = strrpos($this->class, '\\')) ? $this->class : substr($this->class, $i + 1)))));
    }

    /**
     * @return DomainCollection<array-key, T>
     */
    private function doFindAll(int $offset = 0, int $limit = 0): DomainCollection
    {
        return $this->createResultSet($this->createQueryBuilder()->getQuery(), $offset, $limit);
    }

    /**
     * @return DomainCollection<array-key, T>
     */
    private function doFindAllByFields(array $fields, int $offset = 0, int $limit = 0): DomainCollection
    {
        if (!$fields) {
            throw new \LogicException('No fields provided.');
        }

        $this->addFieldCriteria($qb = $this->createQueryBuilder(), $fields);

        return $this->createResultSet($qb->getQuery(), $offset, $limit);
    }

    /**
     * @param mixed $id
     *
     * @return T
     */
    private function doFind($id): object
    {
        $id = $this->toIdentity($id);
        $entity = null === $id ? null : $this->em->find($this->class, $id);

        if (null === $entity) {
            throw EntityNotFound::createForId($this->class, $id);
        }

        return $entity;
    }

    /**
     * @return T
     */
    private function doFindByFields(array $fields): object
    {
        if (!$fields) {
            throw new \LogicException('No fields provided.');
        }

        $this->addFieldCriteria($qb = $this->createQueryBuilder(), $fields);
        $qb->setFirstResult(0);
        $qb->setMaxResults(1);

        if (null === $entity = $qb->getQuery()->getOneOrNullResult()) {
            throw EntityNotFound::createForFields($this->class, $fields);
        }

        return $entity;
    }

    /**
     * @param mixed $id
     */
    private function doExists($id): bool
    {
        if (null === $id = $this->toIdentity($id)) {
            return false;
        }

        return $this->doExistsByFields($id);
    }

    private function doExistsByFields(array $fields): bool
    {
        if (!$fields) {
            throw new \LogicException('No fields provided.');
        }

        $this->addFieldCriteria($qb = $this->createQueryBuilder(), $fields);
        $qb->select('1');
        $qb->setFirstResult(0);
        $qb->setMaxResults(1);

        return (bool) $qb->getQuery()->getScalarResult();
    }

    /**
     * @param T $entity
     */
    private function doSave(object $entity): void
    {
        $this->em->persist($entity);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DuplicateEntity::createForId(\get_class($entity), $this->em->getMetadataFactory()->getMetadataFor($this->class)->getIdentifierValues($entity));
        }
    }

    /**
     * @param T $entity
     */
    private function doDelete(object $entity): void
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
     * @template T2Key of array-key
     * @template T2 of object
     *
     * @param int|string $hydrate
     *
     * @return DomainCollection<T2Key, T2>
     */
    private function createResultSet(Query $query, ?int $offset = null, ?int $limit = null, $hydrate = Query::HYDRATE_OBJECT): DomainCollection
    {
        if (null !== $offset || !$query->getFirstResult()) {
            $query->setFirstResult($offset ?? 0);
        }

        if (null !== $limit) {
            $query->setMaxResults(0 === $limit ? null : $limit);
        }

        /** @var DomainCollection<T2Key, T2> */
        return new GenericDomainCollection($query->getResult($hydrate));
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select($alias = $this->getAlias());
        $qb->from($this->class, $alias);

        return $qb;
    }

    private function addFieldCriteria(QueryBuilder $qb, array $fields, bool $or = false): void
    {
        if (!$fields) {
            return;
        }

        $expr = $qb->expr();
        $where = $or ? $expr->orX() : $expr->andX();
        $alias = $this->getAlias();
        $associations = $this->em->getClassMetadata($this->class)->getAssociationMappings();

        foreach ($fields as $field => $value) {
            $fieldAlias = $alias.'.'.$field;

            if (null === $value) {
                $where->add($expr->isNull($fieldAlias));

                continue;
            }

            if (true === $value || false === $value) {
                $where->add($expr->eq($fieldAlias, $value ? 'TRUE' : 'FALSE'));

                continue;
            }

            $param = $this->addFieldParameter($qb, (string) $field, $value);

            if (\is_array($value)) {
                $where->add($expr->in($fieldAlias, $param));
            } elseif (isset($associations[$field])) {
                $where->add($expr->eq('IDENTITY('.$fieldAlias.')', $param));
            } else {
                $where->add($expr->eq($fieldAlias, $param));
            }
        }

        $qb->andWhere($where);
    }

    /**
     * @param mixed $value
     */
    private function addFieldParameter(QueryBuilder $qb, string $field, $value): string
    {
        $name = $base = str_replace('.', '_', $field);
        $counter = 0;

        while (null !== $qb->getParameter($name)) {
            $name = $base.++$counter;
        }

        $qb->setParameter($name, $this->castFieldValue($field, $this->castFieldValue($field, $value)));

        return ':'.$name;
    }

    /**
     * @param mixed $id
     */
    private function toIdentity($id): ?array
    {
        $metadata = $this->em->getClassMetadata($this->class);
        $fields = $metadata->getIdentifierFieldNames();

        if (!\is_array($id)) {
            if (1 !== \count($fields)) {
                return null;
            }

            $id = [reset($fields) => $id];
        }

        foreach ($id as $field => $value) {
            $value = $this->castFieldValue((string) $field, $value);

            if (\is_object($value)) {
                try {
                    $value = $this->em->getUnitOfWork()->getSingleIdentifierValue($value);
                } catch (MappingException $e) {
                }
            }

            $id[$field] = $value;
        }

        $identity = [];
        foreach ($fields as $field) {
            if (null === ($id[$field] ?? null)) {
                return null;
            }

            $identity[$field] = $id[$field];
            unset($id[$field]);
        }

        return !$id && $identity ? $identity : null;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function castFieldValue(string $field, $value)
    {
        if (!$value instanceof DomainId) {
            return $value;
        }

        $value = $value->isNil() ? null : $value->toString();

        if (null === $type = $this->em->getClassMetadata($this->class)->getTypeOfField($field)) {
            return $value;
        }

        return Type::getType($type)->convertToPHPValue($value, $this->em->getConnection()->getDatabasePlatform());
    }
}
