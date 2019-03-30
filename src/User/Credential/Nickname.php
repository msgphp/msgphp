<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Event\Domain\ChangeCredential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Nickname implements UsernameCredential
{
    use NicknameAsUsername;

    public function __construct(string $nickname)
    {
        $this->nickname = $nickname;
    }

    public function __invoke(ChangeCredential $event): bool
    {
        [
            'nickname' => $this->nickname,
        ] = $event->fields + $vars = get_object_vars($this);

        return $vars !== get_object_vars($this);
    }
}
