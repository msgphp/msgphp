<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Credential;

use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Entity\Credential\Features\{EmailAsUsername, PasswordWithSaltProtected};
use MsgPhp\User\Password\PasswordProtectedInterface;

/**
 * Note one should prefer a saltless password implementation by default, i.e. {@see EmailPassword}. The salted
 * password implementation is usually used in e.g. a legacy system.
 *
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EmailSaltedPassword implements CredentialInterface, PasswordProtectedInterface
{
    use EmailAsUsername;
    use PasswordWithSaltProtected;

    public function __construct(string $email, string $password, string $passwordSalt)
    {
        $this->email = $email;
        $this->password = $password;
        $this->passwordSalt = $passwordSalt;
    }

    public function withEmail(string $email): self
    {
        return new self($email, $this->password, $this->passwordSalt);
    }

    public function withPassword(string $password): self
    {
        return new self($this->email, $password, $this->passwordSalt);
    }

    public function withPasswordSalt(string $passwordSalt): self
    {
        return new self($this->email, $this->password, $passwordSalt);
    }
}
