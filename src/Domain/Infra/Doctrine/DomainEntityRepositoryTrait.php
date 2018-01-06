<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use MsgPhp\Domain\{DomainCollectionInterface, DomainIdInterface};
use MsgPhp\Domain\Exception\{DuplicateEntityException, EntityNotFoundException};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait DomainEntityRepositoryTrait
{
    private $em;
    private $class;
    private $numIdFields;

    public function __construct(EntityManagerInterface $em, string $class)
    {
        $this->em = $em;
        $this->class = $class;
        $this->numIdFields = count($this->idFields);
    }

    private function isValidEntityId(array $ids): bool
    {
        if (count($ids) !== $this->numIdFields) {
            return false;
        }

        $metadataFactory = $this->em->getMetadataFactory();

        return count(array_filter($ids, function ($id) use ($metadataFactory): bool {
            if ($id instanceof DomainIdInterface) {
                return $id->isKnown();
            }

            if (is_object($id)) {
                $class = get_class($id);

                foreach (($metadata = $metadataFactory->getMetadataFor($this->class))->getIdentifierFieldNames() as $idFieldName) {
                    if ($class === $metadata->getAssociationTargetClass($idFieldName)) {
                        return true;
                    }
                }

                return method_exists($id, '__toString');
            }

            return is_scalar($id);
        })) === $this->numIdFields;
    }

    private function doFindAll(int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        return $this->createResultSet($this->createQueryBuilder()->getQuery(), $offset, $limit);
    }

    private function doFindAllByFields(array $fields, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        $this->addFieldCriteria($qb = $this->createQueryBuilder(), $fields);

        return $this->createResultSet($qb->getQuery(), $offset, $limit);
    }

    private function doFind($id, ...$idN)
    {
        if (!$this->isValidEntityId($ids = func_get_args())) {
            throw EntityNotFoundException::createForFields($this->class, $ids);
        }

        return $this->doFindByFields(array_combine($this->idFields, $ids));
    }

    private function doFindByFields(array $fields)
    {
        if (!$fields) {
            throw new \LogicException('No fields provided.');
        }

        $this->addFieldCriteria($qb = $this->createQueryBuilder(), $fields);
        $qb->setFirstResult(0);
        $qb->setMaxResults(1);

        if (null === $entity = $qb->getQuery()->getOneOrNullResult()) {
            throw EntityNotFoundException::createForFields($this->class, $fields);
        }

        return $entity;
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

        $entityId = ($uow = $this->em->getUnitOfWork())->isInIdentityMap($entity) ? $uow->getEntityIdentifier($entity) : [];

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DuplicateEntityException::createForId(get_class($entity), array_values($entityId));
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

    private function createResultSet(Query $query, int $offset = null, int $limit = null): DomainCollectionInterface
    {
        if (null !== $offset || !$query->getFirstResult()) {
            $query->setFirstResult($offset ?? 0);
        }

        if (null !== $limit) {
            $query->setMaxResults(0 === $limit ? null : $limit);
        }

        return new DomainCollection(new ArrayCollection($query->getResult()));
    }

    private function createQueryBuilder(string $alias = null): QueryBuilder
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select($this->alias);
        $qb->from($this->class, $alias ?? $this->alias);

        return $qb;
    }

    private function addFieldCriteria(QueryBuilder $qb, array $fields, bool $or = false): void
    {
        if (!$fields) {
            return;
        }

        $expr = $qb->expr();
        $where = $or ? $expr->orX() : $expr->andX();
        $metadataFactory = $this->em->getMetadataFactory();

        foreach ($fields as $field => $value) {
            if ($value instanceof DomainIdInterface && !$value->isKnown()) {
                $value = null;
            }

            if (null === $value) {
                $where->add($expr->isNull($this->alias.'.'.$field));
            } elseif (true === $value) {
                $where->add($expr->eq($this->alias.'.'.$field, 'TRUE'));
            } elseif (false === $value) {
                $where->add($expr->eq($this->alias.'.'.$field, 'FALSE'));
            } elseif (is_array($haystack = $value)) {
                // @todo test
                foreach ($haystack as &$value) {
                    $value = $value instanceof DomainIdInterface ? ($value->isKnown() ? $value->toString() : null) : $value;
                }
                unset($value);
                $where->add($expr->in($this->alias.'.'.$field, ':'.($param = uniqid($field))));
                $qb->setParameter($param, $haystack);
            } elseif ($metadataFactory->getMetadataFor($this->class)->hasAssociation($field)) {
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
