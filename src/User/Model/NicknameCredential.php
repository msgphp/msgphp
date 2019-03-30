<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Credential\Nickname;

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

    public function getNickname(): string
    {
        return $this->credential->getUsername();
    }
}
