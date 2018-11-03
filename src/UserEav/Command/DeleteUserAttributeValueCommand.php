<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DeleteUserAttributeValueCommand
{
    public $id;

    final public function __construct($id)
    {
        $this->id = $id;
    }
}
