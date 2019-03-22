<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Credential\Nickname;
use MsgPhp\User\Event\Domain\ChangeCredentialEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait NicknameCredential
{
    use AbstractCredential;

    /**
     * @var Nickname
     */
    private $credential;

    public function getCredential(): Nickname
    {
        return $this->credential;
    }

    public function getNickname(): string
    {
        return $this->credential->getUsername();
    }

    public function changeNickname(string $nickname): void
    {
        ($this->credential)(new ChangeCredentialEvent([Nickname::getUsernameField() => $nickname]));
    }
}
