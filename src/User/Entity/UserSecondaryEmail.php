<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity;

use MsgPhp\Domain\Entity\Features\CanBeConfirmed;
use MsgPhp\User\Entity\Fields\UserField;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class UserSecondaryEmail
{
    use UserField;
    use CanBeConfirmed;

    private $email;
    private $pendingPrimary = false;

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

    public function isPendingPrimary(): bool
    {
        return $this->pendingPrimary;
    }

    public function markPendingPrimary(bool $flag = true): void
    {
        if ($flag && $this->confirmedAt) {
            throw new \LogicException('Cannot mark user secondary e-mail a pending primary as it\'s already confirmed.');
        }

        $this->pendingPrimary = $flag;
    }
}
