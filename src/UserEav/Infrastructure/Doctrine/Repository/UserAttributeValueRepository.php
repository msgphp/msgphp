<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Doctrine\Repository;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Eav\AttributeId;
use MsgPhp\Eav\AttributeValueId;
use MsgPhp\Eav\Infrastructure\Doctrine\Repository\EntityAttributeValueRepositoryTrait;
use MsgPhp\User\Repository\UserAttributeValueRepository as BaseUserAttributeValueRepository;
use MsgPhp\User\UserAttributeValue;
use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserAttributeValueRepository implements BaseUserAttributeValueRepository
{
    use EntityAttributeValueRepositoryTrait;

    /**
     * @return DomainCollection|UserAttributeValue[]
     */
    public function findAllByAttributeId(AttributeId $attributeId, int $offset = 0, int $limit = 0): DomainCollection
    {
        $qb = $this->createQueryBuilder();
        $this->addAttributeCriteria($qb, $attributeId);

        return $this->createResultSet($qb->getQuery(), $offset, $limit);
    }

    /**
     * @return DomainCollection|UserAttributeValue[]
     */
    public function findAllByAttributeIdAndValue(AttributeId $attributeId, $value, int $offset = 0, int $limit = 0): DomainCollection
    {
        $qb = $this->createQueryBuilder();
        $this->addAttributeCriteria($qb, $attributeId, $value);

        return $this->createResultSet($qb->getQuery(), $offset, $limit);
    }

    /**
     * @return DomainCollection|UserAttributeValue[]
     */
    public function findAllByUserId(UserId $userId, int $offset = 0, int $limit = 0): DomainCollection
    {
        return $this->doFindAllByFields(['user' => $userId], $offset, $limit);
    }

    /**
     * @return DomainCollection|UserAttributeValue[]
     */
    public function findAllByUserIdAndAttributeId(UserId $userId, AttributeId $attributeId, int $offset = 0, int $limit = 0): DomainCollection
    {
        $qb = $this->createQueryBuilder();
        $this->addFieldCriteria($qb, ['user' => $userId]);
        $this->addAttributeCriteria($qb, $attributeId);

        return $this->createResultSet($qb->getQuery(), $offset, $limit);
    }

    public function find(AttributeValueId $attributeValueId): UserAttributeValue
    {
        return $this->doFind($attributeValueId);
    }

    public function exists(AttributeValueId $attributeValueId): bool
    {
        return $this->doExists($attributeValueId);
    }

    public function save(UserAttributeValue $userAttributeValue): void
    {
        $this->doSave($userAttributeValue);
    }

    public function delete(UserAttributeValue $userAttributeValue): void
    {
        $this->doDelete($userAttributeValue);
    }
}
