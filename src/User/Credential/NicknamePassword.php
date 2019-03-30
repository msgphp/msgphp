<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Event\Domain\ChangeCredential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class NicknamePassword implements UsernameCredential, PasswordProtectedCredential
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
        [
            'nickname' => $this->nickname,
            'password' => $this->password,
        ] = $event->fields + $vars = get_object_vars($this);

        return $vars !== get_object_vars($this);
    }
}
