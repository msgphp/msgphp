<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class AddUserAttributeValueCommand
{
    public $userId;
    public $attributeId;
    public $value;
    public $context;

    final public function __construct($userId, $attributeId, $value, array $context = [])
    {
        $this->userId = $userId;
        $this->attributeId = $attributeId;
        $this->value = $value;
        $this->context = $context;
    }
}
