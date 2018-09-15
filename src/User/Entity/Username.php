<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity;

use MsgPhp\User\Entity\Fields\UserField;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class Username
{
    use UserField;

    private $username;

    public function __construct(User $user, string $username)
    {
        $this->user = $user;
        $this->username = $username;
    }

    public function __toString(): string
    {
        return $this->username;
    }
}
