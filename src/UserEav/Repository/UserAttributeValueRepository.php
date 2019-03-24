<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Eav\AttributeId;
use MsgPhp\Eav\AttributeValueId;
use MsgPhp\User\UserAttributeValue;
use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserAttributeValueRepository
{
    /**
     * @return DomainCollection|UserAttributeValue[]
     */
    public function findAllByAttributeId(AttributeId $attributeId, int $offset = 0, int $limit = 0): DomainCollection;

    /**
     * @param mixed $value
     *
     * @return DomainCollection|UserAttributeValue[]
     */
    public function findAllByAttributeIdAndValue(AttributeId $attributeId, $value, int $offset = 0, int $limit = 0): DomainCollection;

    /**
     * @return DomainCollection|UserAttributeValue[]
     */
    public function findAllByUserId(UserId $userId, int $offset = 0, int $limit = 0): DomainCollection;

    /**
     * @return DomainCollection|UserAttributeValue[]
     */
    public function findAllByUserIdAndAttributeId(UserId $userId, AttributeId $attributeId, int $offset = 0, int $limit = 0): DomainCollection;

    public function find(AttributeValueId $attributeValueId): UserAttributeValue;

    public function exists(AttributeValueId $attributeValueId): bool;

    public function save(UserAttributeValue $userAttributeValue): void;

    public function delete(UserAttributeValue $userAttributeValue): void;
}
