<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\Entity\Credential\Nickname;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait NicknameCredential
{
    use AbstractCredential;

    /** @var Nickname */
    private $credential;

    public function getCredential(): Nickname
    {
        return $this->credential;
    }

    public function getNickname(): string
    {
        return $this->credential->getNickname();
    }

    public function changeNickname(string $nickname): void
    {
        $this->credential = $this->credential->withNickname($nickname);
    }
}
