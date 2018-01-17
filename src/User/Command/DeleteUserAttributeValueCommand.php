<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\Eav\AttributeValueIdInterface;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserAttributeValueCommand
{
    public $userId;
    public $attributeValueId;

    public function __construct(UserIdInterface $userId, AttributeValueIdInterface $attributeValueId)
    {
        $this->userId = $userId;
        $this->attributeValueId = $attributeValueId;
    }
}
