<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Credential;

use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Entity\Credential\Features\{NicknameAsUsername, PasswordProtected};
use MsgPhp\User\Password\PasswordProtectedInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class NicknamePassword implements CredentialInterface, PasswordProtectedInterface
{
    use NicknameAsUsername;
    use PasswordProtected;

    public function __construct(string $nickname, string $password)
    {
        $this->nickname = $nickname;
        $this->password = $password;
    }

    public function withNickname(string $nickname): self
    {
        return new self($nickname, $this->password);
    }

    public function withPassword(string $password): self
    {
        return new self($this->nickname, $password);
    }
}
