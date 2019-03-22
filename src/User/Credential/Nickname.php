<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Credential\Features\NicknameAsUsername;
use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Event\Domain\ChangeCredentialEvent;

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

    public function __invoke(ChangeCredentialEvent $event): bool
    {
        if ($nicknameChanged = ($this->nickname !== $nickname = $event->getStringField('nickname'))) {
            $this->nickname = $nickname;
        }

        return $nicknameChanged;
    }
}
