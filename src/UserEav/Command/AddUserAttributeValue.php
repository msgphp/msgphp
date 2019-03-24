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
    /**
     * @var UserId
     */
    public $userId;

    /**
     * @var AttributeId
     */
    public $attributeId;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var array
     */
    public $context;

    /**
     * @param mixed $value
     */
    final public function __construct(UserId $userId, AttributeId $attributeId, $value, array $context = [])
    {
        $this->userId = $userId;
        $this->attributeId = $attributeId;
        $this->value = $value;
        $this->context = $context;
    }
}
