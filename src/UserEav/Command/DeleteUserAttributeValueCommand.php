<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\Eav\AttributeValueIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DeleteUserAttributeValueCommand
{
    public $attributeValueId;

    final public function __construct(AttributeValueIdInterface $attributeValueId)
    {
        $this->attributeValueId = $attributeValueId;
    }
}
