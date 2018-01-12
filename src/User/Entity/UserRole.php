<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity;

use MsgPhp\User\Entity\Fields\UserField;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class UserRole
{
    use UserField;

    private $role;

    public function __construct(User $user, string $role)
    {
        $this->user = $user;
        $this->role = $role;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
