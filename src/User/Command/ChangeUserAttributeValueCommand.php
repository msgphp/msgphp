<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\Eav\AttributeValueIdInterface;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserAttributeValueCommand
{
    public $userId;
    public $attributeValueId;
    public $value;

    public function __construct(UserIdInterface $userId, AttributeValueIdInterface $attributeValueId, $value)
    {
        $this->userId = $userId;
        $this->attributeValueId = $attributeValueId;
        $this->value = $value;
    }
}
