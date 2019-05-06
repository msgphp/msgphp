<?php

declare(strict_types=1);

namespace MsgPhp\User;

use MsgPhp\User\Model\UserField;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class Username
{
    use UserField;

    /**
     * @var string
     */
    private $username;

    public function __construct(User $user, string $username)
    {
        $this->user = $user;
        $this->username = $username;
    }

    public function toString(): string
    {
        return $this->username;
    }
}
