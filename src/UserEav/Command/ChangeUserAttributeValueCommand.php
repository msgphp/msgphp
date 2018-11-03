<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ChangeUserAttributeValueCommand
{
    public $id;
    public $value;

    final public function __construct($id, $value)
    {
        $this->id = $id;
        $this->value = $value;
    }
}
