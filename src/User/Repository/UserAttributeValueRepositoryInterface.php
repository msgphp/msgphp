<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Eav\{AttributeIdInterface, AttributeValueIdInterface};
use MsgPhp\User\Entity\UserAttributeValue;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserAttributeValueRepositoryInterface
{
    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function findAllByAttributeId(AttributeIdInterface $attributeId, int $offset = 0, int $limit = 0): DomainCollectionInterface;

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function findAllByAttributeIdAndValue(AttributeIdInterface $attributeId, $value, int $offset = 0, int $limit = 0): DomainCollectionInterface;

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function findAllByUserId(UserIdInterface $userId, int $offset = 0, int $limit = 0): DomainCollectionInterface;

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function findAllByUserIdAndAttributeId(UserIdInterface $userId, AttributeIdInterface $attributeId, int $offset = 0, int $limit = 0): DomainCollectionInterface;

    public function find(AttributeValueIdInterface $attributeValueId): UserAttributeValue;

    public function exists(AttributeValueIdInterface $attributeValueId): bool;

    public function save(UserAttributeValue $userAttributeValue): void;

    public function delete(UserAttributeValue $userAttributeValue): void;
}
