<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\GenericDomainCollection;
use MsgPhp\User\UserAttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait AttributeValuesField
{
    /** @var iterable<array-key, UserAttributeValue> */
    private $attributeValues = [];

    /**
     * @return DomainCollection<array-key, UserAttributeValue>
     */
    public function getAttributeValues(): DomainCollection
    {
        return new GenericDomainCollection($this->attributeValues);
    }
}
