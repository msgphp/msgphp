<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DeleteUserAttributeValueCommand
{
    public $attributeValueId;

    final public function __construct($attributeValueId)
    {
        $this->attributeValueId = $attributeValueId;
    }
}
