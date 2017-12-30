<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Credential;

use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Entity\Credential\Features\EmailAsUsername;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Email implements CredentialInterface
{
    use EmailAsUsername;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function withEmail(string $email): self
    {
        return new self($email);
    }
}
