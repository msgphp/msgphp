<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class CreateRoleCommand
{
    public $context;

    final public function __construct(array $context)
    {
        $this->context = $context;
    }
}
