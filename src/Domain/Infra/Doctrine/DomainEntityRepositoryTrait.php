<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use MsgPhp\Domain\{DomainCollection, DomainCollectionInterface};
use MsgPhp\Domain\Exception\{DuplicateEntityException, EntityNotFoundException, InvalidClassException};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait DomainEntityRepositoryTrait
{
    private $class;
    private $em;
    private $alias;

    public function __construct(string $class, EntityManagerInterface $em)
    {
        $this->class = $class;
        $this->em = $em;
    }

    private function getAlias(): string
    {
        return $this->alias ?? ($this->alias = strtolower((string) preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1_\\2', '\\1_\\2'], (string) (false === ($i = strrpos($this->class, '\\')) ? $this->class : substr($this->class, $i + 1)))));
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
    private function doFind($id)
    {
        if (null === $entity = $this->em->find($this->class, $id)) {
            throw EntityNotFoundException::createForId($this->class, $id);
        }

        return $entity;
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
            throw EntityNotFoundException::createForFields($this->class, $fields);
        }

        return $entity;
    }

    private function doExists($id): bool
    {
        $id = $this->toIdentity($id);

        if (null === $id) {
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
     * @param object $entity
     */
    private function doSave($entity): void
    {
        if (!$entity instanceof $this->class) {
            throw InvalidClassException::create(\get_class($entity));
        }

        $this->em->persist($entity);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DuplicateEntityException::createForId(\get_class($entity), $this->em->getMetadataFactory()->getMetadataFor($this->class)->getIdentifierValues($entity));
        }
    }

    /**
     * @param object $entity
     */
    private function doDelete($entity): void
    {
        if (!$entity instanceof $this->class) {
            throw InvalidClassException::create(\get_class($entity));
        }

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

        return new DomainCollection($query->getResult($hydrate));
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

    private function addFieldParameter(QueryBuilder $qb, string $field, $value, string $type = null): string
    {
        $name = $base = str_replace('.', '_', $field);
        $counter = 0;

        while (null !== $qb->getParameter($name)) {
            $name = $base.++$counter;
        }

        $qb->setParameter($name, $value, $type ?? DomainIdType::resolveName($value));

        return ':'.$name;
    }

    private function toIdentity($id): ?array
    {
        $metadataFactory = $this->em->getMetadataFactory();
        $metadata = $metadataFactory->getMetadataFor($this->class);
        $fields = $metadata->getIdentifierFieldNames();

        if (!\is_array($id)) {
            if (1 !== \count($fields)) {
                return null;
            }

            $id = [reset($fields) => $id];
        }

        foreach ($id as $field => $value) {
            if (\is_object($value) && $metadataFactory->hasMetadataFor(ClassUtils::getClass($value))) {
                $id[$field] = $this->em->getUnitOfWork()->getSingleIdentifierValue($value);

                if (null === $id[$field]) {
                    return null;
                }
            }
        }

        $identity = [];
        foreach ($fields as $field) {
            if (!isset($id[$field])) {
                return null;
            }

            $identity[$field] = $id[$field];
            unset($id[$field]);
        }

        return $identity ?: null;
    }
}
