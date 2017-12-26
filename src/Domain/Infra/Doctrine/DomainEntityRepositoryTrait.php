<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use MsgPhp\Domain\Exception\DuplicateEntityException;
use MsgPhp\Domain\Exception\EntityNotFoundException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait DomainEntityRepositoryTrait
{
    private $em;
    private $class;

    public function __construct(EntityManagerInterface $em, string $class)
    {
        $this->em = $em;
        $this->class = $class;
    }

    private function doFind($id, ...$idN)
    {
        return $this->doFindByFields(array_combine($this->idFields, func_get_args()));
    }

    private function doFindByFields(array $fields)
    {
        $qb = $this->createQueryBuilder(null, 1);
        $this->addFieldCriteria($qb, $fields);

        if (null === $entity = $qb->getQuery()->getOneOrNullResult()) {
            throw EntityNotFoundException::createForFields($this->class, $fields);
        }

        return $entity;
    }

    private function doExists($id, ...$idN): bool
    {
        return $this->doExistsByFields(array_combine($this->idFields, func_get_args()));
    }

    private function doExistsByFields(array $fields): bool
    {
        $qb = $this->createQueryBuilder(null, 1);
        $qb->select('1');
        $this->addFieldCriteria($qb, $fields);

        return (bool) $qb->getQuery()->getScalarResult();
    }

    private function doSave($entity): void
    {
        $this->em->persist($entity);

        $entityId = $this->em->getUnitOfWork()->getEntityIdentifier($entity);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DuplicateEntityException::createForId(get_class($entity), array_values($entityId));
        }
    }

    private function doDelete($entity): void
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    private function createResultSet(Query $query): DomainEntityCollection
    {
        return new DomainEntityCollection(new ArrayCollection($query->getResult()));
    }

    private function createQueryBuilder(int $offset = null, int $limit = null): QueryBuilder
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select($this->alias);
        $qb->from($this->class, $this->alias);

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    private function addFieldCriteria(QueryBuilder $qb, array $fields, $or = false): void
    {
        $expr = $qb->expr();
        $where = $or ? $expr->orX() : $expr->andX();

        foreach ($fields as $field => $value) {
            if (null === $value) {
                $where->add($expr->isNull($this->alias.'.'.$field));
            } elseif (true === $value) {
                $where->add($expr->eq($this->alias.'.'.$field, 'TRUE'));
            } elseif (false === $value) {
                $where->add($expr->eq($this->alias.'.'.$field, 'FALSE'));
            } elseif (is_array($value)) {
                $where->add($expr->in($this->alias.'.'.$field, ':'.($param = uniqid($field))));
                $qb->setParameter($param, $value);
            } elseif ($this->em->getMetadataFactory()->getMetadataFor($this->class)->hasAssociation($field)) {
                $where->add($expr->eq('IDENTITY('.$this->alias.'.'.$field.')', ':'.($param = uniqid($field))));
                $qb->setParameter($param, $value);
            } else {
                $where->add($expr->eq($this->alias.'.'.$field, ':'.($param = uniqid($field))));
                $qb->setParameter($param, $value);
            }
        }

        $qb->andWhere($where);
    }
}
