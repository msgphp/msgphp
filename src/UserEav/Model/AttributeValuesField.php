<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\GenericDomainCollection;
use MsgPhp\User\UserAttributeValue;

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
        return new GenericDomainCollection($this->attributeValues);
    }
}
