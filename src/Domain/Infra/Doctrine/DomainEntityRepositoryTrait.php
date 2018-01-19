<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use MsgPhp\Domain\{AbstractDomainEntityRepositoryTrait, DomainCollectionInterface};
use MsgPhp\Domain\Factory\DomainCollectionFactory;
use MsgPhp\Domain\Exception\{DuplicateEntityException, EntityNotFoundException};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait DomainEntityRepositoryTrait
{
    use AbstractDomainEntityRepositoryTrait;

    private $em;

    public function __construct(string $class, EntityManagerInterface $em, array $fieldMapping = [])
    {
        $this->class = $class;
        $this->identityMapping = new DomainIdentityMapping($em);
        $this->fieldMapping = $fieldMapping;
        $this->em = $em;
    }

    private function doFindAll(int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        return $this->createResultSet($this->createQueryBuilder()->getQuery(), $offset, $limit);
    }

    private function doFindAllByFields(array $fields, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        if (!$fields) {
            throw new \LogicException('No fields provided.');
        }

        $this->addFieldCriteria($qb = $this->createQueryBuilder(), $fields);

        return $this->createResultSet($qb->getQuery(), $offset, $limit);
    }

    /**
     * @return object
     */
    private function doFind($id, ...$idN)
    {
        $identity = $this->toIdentity(...$ids = func_get_args());

        if (null === $identity) {
            throw EntityNotFoundException::createForId($this->class, ...$ids);
        }

        return $this->doFindByFields($identity);
    }

    /**
     * @return object
     */
    private function doFindByFields(array $fields)
    {
        if (!$fields) {
            throw new \LogicException('No fields provided.');
        }

        $this->addFieldCriteria($qb = $this->createQueryBuilder(), $fields);
        $qb->setFirstResult(0);
        $qb->setMaxResults(1);

        if (null === $entity = $qb->getQuery()->getOneOrNullResult()) {
            if ($this->isIdentity($fields)) {
                throw EntityNotFoundException::createForId($this->class, ...array_values($fields));
            }

            throw EntityNotFoundException::createForFields($this->class, $fields);
        }

        return $entity;
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
     * @param object $entity
     */
    private function doSave($entity): void
    {
        $this->em->persist($entity);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DuplicateEntityException::createForId(get_class($entity), ...array_values($this->em->getClassMetadata($this->class)->getIdentifierValues($entity)));
        }
    }

    /**
     * @param object $entity
     */
    private function doDelete($entity): void
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    private function createResultSet(Query $query, int $offset = null, int $limit = null, $hydrate = Query::HYDRATE_OBJECT): DomainCollectionInterface
    {
        if (null !== $offset || !$query->getFirstResult()) {
            $query->setFirstResult($offset ?? 0);
        }

        if (null !== $limit) {
            $query->setMaxResults(0 === $limit ? null : $limit);
        }

        return DomainCollectionFactory::create($query->getResult($hydrate));
    }

    private function createQueryBuilder(string $alias = null): QueryBuilder
    {
        if (null === $alias) {
            $alias = $this->alias;
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select($alias);
        $qb->from($this->class, $alias);

        return $qb;
    }

    private function addFieldCriteria(QueryBuilder $qb, array $fields, bool $or = false, string $alias = null): void
    {
        if (!$fields) {
            return;
        }

        $expr = $qb->expr();
        $where = $or ? $expr->orX() : $expr->andX();
        $alias = $alias ?? $qb->getAllAliases()[0] ?? $this->alias;
        $metadata = $this->em->getClassMetadata($this->class);

        foreach ($this->mapFields($fields) as $field => $value) {
            $fieldAlias = $alias.'.'.$field;
            $value = $this->normalizeIdentifier($value);

            if (null === $value) {
                $where->add($expr->isNull($fieldAlias));
            } elseif (true === $value) {
                $where->add($expr->eq($fieldAlias, 'TRUE'));
            } elseif (false === $value) {
                $where->add($expr->eq($fieldAlias, 'FALSE'));
            } elseif (is_array($value)) {
                $where->add($expr->in($fieldAlias, ':'.($param = self::getPlaceholder($field))));
                $qb->setParameter($param, $value);
            } elseif ($metadata->hasAssociation($field)) {
                $where->add($expr->eq('IDENTITY('.$fieldAlias.')', ':'.($param = self::getPlaceholder($field))));
                $qb->setParameter($param, $value);
            } else {
                $where->add($expr->eq($fieldAlias, ':'.($param = self::getPlaceholder($field))));
                $qb->setParameter($param, $value);
            }
        }

        $qb->andWhere($where);
    }

    private static function getPlaceholder(string $name): string
    {
        return uniqid(strtr($name, ['.' => '_', '[' => '_', ']' => '_']));
    }
}
