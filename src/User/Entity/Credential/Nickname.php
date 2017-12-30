<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Credential;

use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Entity\Credential\Features\NicknameAsUsername;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Nickname implements CredentialInterface
{
    use NicknameAsUsername;

    public function __construct(string $nickname)
    {
        $this->nickname = $nickname;
    }

    public function withNickname(string $nickname): self
    {
        return new self($nickname);
    }
}
