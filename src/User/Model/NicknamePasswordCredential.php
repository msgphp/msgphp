<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Credential\NicknamePassword;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait NicknamePasswordCredential
{
    use NicknameCredential;

    /**
     * @var NicknamePassword
     */
    private $credential;

    public function getPassword(): string
    {
        return $this->credential->getPassword();
    }
}
