<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\UserAttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class UserAttributeValueChanged
{
    public $userAttributeValue;

    /** @var mixed */
    public $oldValue;

    /** @var mixed */
    public $newValue;

    /**
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    public function __construct(UserAttributeValue $userAttributeValue, $oldValue, $newValue)
    {
        $this->userAttributeValue = $userAttributeValue;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }
}
