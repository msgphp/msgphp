<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use MsgPhp\User\Infra\Validator\EmailLookupInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SqlEmailLookup implements EmailLookupInterface
{
    private $em;
    private $entityFieldMapping;
    private $primaryEntityFieldMapping;

    public function __construct(EntityManagerInterface $em, array $entityFieldMapping, array $primaryEntityFieldMapping = [])
    {
        $this->em = $em;
        $this->entityFieldMapping = $entityFieldMapping;
        $this->primaryEntityFieldMapping = $primaryEntityFieldMapping ?: $entityFieldMapping;
    }

    public function exists(string $email): bool
    {
        return (bool) $this->createQuery($this->entityFieldMapping, $email)->getScalarResult();
    }

    public function existsPrimary(string $email): bool
    {
        return (bool) $this->createQuery($this->primaryEntityFieldMapping, $email)->getScalarResult();
    }

    private function createSql(string $entity, string $field): string
    {
        $metadata = $this->em->getMetadataFactory()->getMetadataFor($entity);

        if (!$metadata instanceof ClassMetadataInfo) {
            throw new \UnexpectedValueException(sprintf('Expected instance of "%s", got "%s".', ClassMetadataInfo::class, get_class($metadata)));
        }

        return sprintf('SELECT 1 FROM %s WHERE %s = :email', $metadata->getTableName(), $metadata->getColumnName($field));
    }

    private function createQuery(array $entities, string $email): NativeQuery
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('1', '1');

        $sql = implode(' UNION ', array_map(function ($entity, $field) {
            return $this->createSql($entity, $field);
        }, array_keys($entities), $entities));

        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter('email', $email);

        return $query;
    }
}
