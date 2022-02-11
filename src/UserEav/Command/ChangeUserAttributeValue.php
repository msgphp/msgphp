<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\Eav\AttributeValueId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ChangeUserAttributeValue
{
    public $attributeValueId;

    /** @var mixed */
    public $value;

    /**
     * @param mixed $value
     */
    public function __construct(AttributeValueId $attributeValueId, $value)
    {
        $this->attributeValueId = $attributeValueId;
        $this->value = $value;
    }
}
