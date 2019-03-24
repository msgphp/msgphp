<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DeleteUserEmail
{
    /**
     * @var string
     */
    public $email;

    final public function __construct(string $email)
    {
        $this->email = $email;
    }
}
