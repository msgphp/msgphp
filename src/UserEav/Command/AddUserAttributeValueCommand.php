<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\Eav\AttributeIdInterface;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class AddUserAttributeValueCommand
{
    public $userId;
    public $attributeId;
    public $value;
    public $context;

    final public function __construct(UserIdInterface $userId, AttributeIdInterface $attributeId, $value, array $context = [])
    {
        $this->userId = $userId;
        $this->attributeId = $attributeId;
        $this->value = $value;
        $this->context = $context;
    }
}
