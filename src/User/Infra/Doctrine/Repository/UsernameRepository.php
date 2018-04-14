<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\{DomainCollectionInterface, DomainIdentityHelper};
use MsgPhp\Domain\Factory\DomainCollectionFactory;
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\User\Entity\{User, Username};
use MsgPhp\User\Repository\UsernameRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UsernameRepository implements UsernameRepositoryInterface
{
    use DomainEntityRepositoryTrait {
        __construct as private __parent_construct;
    }

    private $alias = 'username';
    private $targetMapping;

    public function __construct(string $class, EntityManagerInterface $em, array $targetMapping = [], DomainIdentityHelper $identityHelper = null)
    {
        $this->__parent_construct($class, $em, $identityHelper);

        $this->targetMapping = $targetMapping;
    }

    /**
     * @return DomainCollectionInterface|Username[]
     */
    public function findAll(int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        $qb = $this->createQueryBuilder();
        $qb->indexBy($this->alias, 'username');

        return $this->createResultSet($qb->getQuery(), $offset, $limit);
    }

    /**
     * @return DomainCollectionInterface|Username[]
     */
    public function findAllFromTargets(int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        if (!$this->targetMapping) {
            throw new \LogicException('No username mapping available.');
        }

        $results = $targets = [];
        foreach ($this->targetMapping as $class => $mappings) {
            $qb = $this->em->createQueryBuilder();
            $metadata = $this->em->getClassMetadata($class);
            $fields = array_flip($idFields = $metadata->getIdentifierFieldNames());

            foreach ($mappings as $field => $mappedBy) {
                $targets[$class][$field] = $mappedBy;
                $fields[$field] = true;

                if (null !== $mappedBy) {
                    $fields[$mappedBy] = true;
                }
            }
            foreach ((array) $metadata->discriminatorMap as $discriminatorClass) {
                if (isset($targets[$discriminatorClass])) {
                    $targets[$discriminatorClass] += $targets[$class];
                } else {
                    $targets[$discriminatorClass] = $targets[$class];
                }
            }

            $qb->addSelect(sprintf('partial e.{%s}', implode(', ', array_keys($fields))));
            $qb->from($class, 'e');

            $results[] = $qb->getQuery()->getResult();
        }

        $result = [];
        foreach ($results ? array_merge(...$results) : [] as $target) {
            $metadata = $this->em->getClassMetadata($class = ClassUtils::getRealClass(get_class($target)));

            foreach ($targets[$class] as $field => $mappedBy) {
                $user = null === $mappedBy ? $target : $metadata->getFieldValue($target, $mappedBy);

                if (null === $user) {
                    continue;
                }

                if (!$user instanceof User) {
                    throw new \LogicException(sprintf('Field "%s.%s" must return an instance of "%s" or null, got "%s".', $class, $field, get_class($target), is_object($user) ? get_class($user) : gettype($user)));
                }

                if (null !== $username = $metadata->getFieldValue($target, $field)) {
                    $result[] = new $this->class($user, $username);
                }
            }
        }

        $result = DomainCollectionFactory::create($result);

        return $offset || $limit ? $result->slice($offset, $limit) : $result;
    }

    public function find(string $username): Username
    {
        return $this->doFind($username);
    }

    public function exists(string $username): bool
    {
        return $this->doExists($username);
    }

    public function save(Username $user): void
    {
        $this->doSave($user);
    }

    public function delete(Username $user): void
    {
        $this->doDelete($user);
    }
}
