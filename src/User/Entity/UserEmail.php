<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity;

use MsgPhp\User\Entity\Fields\UserField;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class UserEmail
{
    use UserField;

    private $email;

    public function __construct(User $user, string $email)
    {
        $this->user = $user;
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
