<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity;

use MsgPhp\Domain\Entity\Features\CanBeConfirmed;
use MsgPhp\User\Entity\Fields\UserField;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class UserEmail
{
    use UserField;
    use CanBeConfirmed;

    private $email;

    public function __construct(User $user, string $email, string $token = null)
    {
        $this->user = $user;
        $this->email = $email;
        $this->confirmationToken = $token ?? bin2hex(random_bytes(32));
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
