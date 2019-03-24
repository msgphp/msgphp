<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Event\Domain\ChangeCredential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Nickname implements Credential
{
    use NicknameAsUsername;

    public function __construct(string $nickname)
    {
        $this->nickname = $nickname;
    }

    public function __invoke(ChangeCredential $event): bool
    {
        if ($nicknameChanged = ($this->nickname !== $nickname = $event->getStringField('nickname'))) {
            $this->nickname = $nickname;
        }

        return $nicknameChanged;
    }
}
