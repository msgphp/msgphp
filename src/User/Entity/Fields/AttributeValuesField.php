<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Fields;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Factory\DomainCollectionFactory;
use MsgPhp\Eav\Entity\Fields\AttributesField;
use MsgPhp\User\Entity\UserAttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait AttributeValuesField
{
    use AttributesField;

    /** @var UserAttributeValue[] */
    private $attributeValues = [];

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function getAttributeValues(): DomainCollectionInterface
    {
        return $this->attributeValues instanceof DomainCollectionInterface ? $this->attributeValues : DomainCollectionFactory::create($this->attributeValues);
    }
}
