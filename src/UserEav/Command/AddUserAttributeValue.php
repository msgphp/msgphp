<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\Eav\AttributeId;
use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class AddUserAttributeValue
{
    public $userId;
    public $attributeId;

    /** @var mixed */
    public $value;
    public $context;

    /**
     * @param mixed $value
     */
    public function __construct(UserId $userId, AttributeId $attributeId, $value, array $context = [])
    {
        $this->userId = $userId;
        $this->attributeId = $attributeId;
        $this->value = $value;
        $this->context = $context;
    }
}
