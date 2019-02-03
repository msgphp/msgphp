<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Fields;

use MsgPhp\Domain\{DomainCollection, DomainCollectionInterface};
use MsgPhp\User\Entity\UserAttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait AttributeValuesField
{
    /**
     * @var iterable|UserAttributeValue[]
     */
    private $attributeValues = [];

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function getAttributeValues(): DomainCollectionInterface
    {
        return new DomainCollection($this->attributeValues);
    }
}
