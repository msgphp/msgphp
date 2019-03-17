<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity;

use MsgPhp\Eav\Entity\AttributeValue;
use MsgPhp\Eav\Model\EntityAttributeValue;
use MsgPhp\User\Model\UserField;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class UserAttributeValue
{
    use UserField;
    use EntityAttributeValue;

    public function __construct(User $user, AttributeValue $attributeValue)
    {
        $this->user = $user;
        $this->attributeValue = $attributeValue;
    }
}
