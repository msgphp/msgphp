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
use MsgPhp\User\UserIdInterface;

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
            throw new \LogicException('Cannot find usernames from targets, no mapping available.');
        }

        $qb = $this->em->createQueryBuilder();
        $aliases = $usernames = [];
        foreach ($this->targetMapping as $class => $mappings) {
            $metadata = $this->em->getClassMetadata($class);
            $alias = $aliases[$class] ?? ($aliases[$class] = 'target'.count($aliases));

            foreach ($mappings as $mapping) {
                $fields = array_flip($idFields = $metadata->getIdentifierFieldNames());

                if (!isset($fields[$mapping['field']])) {
                    $fields[$mapping['field']] = true;
                }

                if (isset($mapping['mapped_by'])) {
                    if (!isset($fields[$mapping['mapped_by']])) {
                        $fields[$mapping['mapped_by']] = true;
                    }

                    $userField = $mapping['mapped_by'];
                } else {
                    $userField = reset($idFields);
                }

                $qb->addSelect(sprintf('partial %s.{%s}', $alias, implode(', ', array_keys($fields))));
                $qb->from($mapping['target'], $alias);

                $usernames[$class][] = ['user' => $userField, 'username' => $mapping['field']];
            }
        }

        $result = [];
        foreach ($qb->getQuery()->getResult() as $targetEntity) {
            $metadata = $this->em->getClassMetadata($class = ClassUtils::getRealClass(get_class($targetEntity)));

            foreach ($usernames[$class] as $username) {
                $user = $metadata->getFieldValue($targetEntity, $username['user']);
                if (!$user instanceof User) {
                    $user = $this->em->getPartialReference(User::class, $user);
                }

                $result[] = new Username($user, $metadata->getFieldValue($targetEntity, $username['username']));
            }
        }

        $result = DomainCollectionFactory::create($result);

        return $offset || $limit ? $result->slice($offset, $limit) : $result;
    }

    public function find(UserIdInterface $userId, string $username): Username
    {
        return $this->doFind(...func_get_args());
    }

    public function exists(UserIdInterface $userId, string $username): bool
    {
        return $this->doExists(...func_get_args());
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
