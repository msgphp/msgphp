<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\Entity\EntityCollectionInterface;
use MsgPhp\Eav\{AttributeIdInterface, AttributeValueIdInterface};
use MsgPhp\User\Entity\UserAttributeValue;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserAttributeValueRepositoryInterface
{
    /**
     * @return EntityCollectionInterface|UserAttributeValue[]
     */
    public function findAllByAttributeId(AttributeIdInterface $attributeId, int $offset = null, int $limit = null): EntityCollectionInterface;

    /**
     * @return EntityCollectionInterface|UserAttributeValue[]
     */
    public function findAllByAttributeIdAndValue(AttributeIdInterface $attributeId, $value, int $offset = null, int $limit = null): EntityCollectionInterface;

    /**
     * @return EntityCollectionInterface|UserAttributeValue[]
     */
    public function findAllByUserId(UserIdInterface $userId, int $offset = null, int $limit = null): EntityCollectionInterface;

    /**
     * @return EntityCollectionInterface|UserAttributeValue[]
     */
    public function findAllByUserIdAndAttributeId(UserIdInterface $userId, AttributeIdInterface $attributeId, int $offset = null, int $limit = null): EntityCollectionInterface;

    public function find(UserIdInterface $userId, AttributeValueIdInterface $attributeValueId): UserAttributeValue;

    public function exists(UserIdInterface $userId, AttributeValueIdInterface $attributeValueId): bool;

    /**
     * @internal
     */
    public function save(UserAttributeValue $userAttributeValue): void;

    /**
     * @internal
     */
    public function delete(UserAttributeValue $userAttributeValue): void;
}
