<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\InMemory\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Infra\InMemory\DomainEntityRepositoryTrait;
use MsgPhp\Eav\{AttributeIdInterface, AttributeValueIdInterface};
use MsgPhp\User\Entity\UserAttributeValue;
use MsgPhp\User\Repository\UserAttributeValueRepositoryInterface;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserAttributeValueRepository implements UserAttributeValueRepositoryInterface
{
    use DomainEntityRepositoryTrait;

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function findAllByAttributeId(AttributeIdInterface $attributeId, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        $entities = $this->doFindAll()->filter(function (UserAttributeValue $userAttributeValue) use ($attributeId): bool {
            return $userAttributeValue->getAttributeId()->equals($attributeId);
        });

        return $this->createResultSet($entities, $offset, $limit);
    }

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function findAllByAttributeIdAndValue(AttributeIdInterface $attributeId, $value, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        $entities = $this->doFindAll()->filter(function (UserAttributeValue $userAttributeValue) use ($attributeId, $value): bool {
            return $userAttributeValue->getAttributeId()->equals($attributeId) && $value === $userAttributeValue->getValue();
        });

        return $this->createResultSet($entities, $offset, $limit);
    }

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function findAllByUserId(UserIdInterface $userId, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        return $this->doFindAllByFields(['userId' => $userId], $offset, $limit);
    }

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function findAllByUserIdAndAttributeId(UserIdInterface $userId, AttributeIdInterface $attributeId, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        $entities = $this->doFindAll()->filter(function (UserAttributeValue $userAttributeValue) use ($userId, $attributeId): bool {
            return $userAttributeValue->getUserId()->equals($userId) && $userAttributeValue->getAttributeId()->equals($attributeId);
        });

        return $this->createResultSet($entities, $offset, $limit);
    }

    public function find(UserIdInterface $userId, AttributeValueIdInterface $attributeValueId): UserAttributeValue
    {
        return $this->doFind(...func_get_args());
    }

    public function exists(UserIdInterface $userId, AttributeValueIdInterface $attributeValueId): bool
    {
        return $this->doExists(...func_get_args());
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
