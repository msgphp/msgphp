<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Event\Domain\ChangeCredential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class NicknamePassword implements PasswordProtectedCredential
{
    use NicknameAsUsername;
    use PasswordProtection;

    public function __construct(string $nickname, string $password)
    {
        $this->nickname = $nickname;
        $this->password = $password;
    }

    public function __invoke(ChangeCredential $event): bool
    {
        if ($nicknameChanged = ($this->nickname !== $nickname = $event->getStringField('nickname'))) {
            $this->nickname = $nickname;
        }
        if ($passwordChanged = ($this->password !== $password = $event->getStringField('password'))) {
            $this->password = $password;
        }

        return $nicknameChanged || $passwordChanged;
    }
}
