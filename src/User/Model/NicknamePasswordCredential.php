<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Credential\NicknamePassword;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait NicknamePasswordCredential
{
    use AbstractPasswordCredential;
    use NicknameCredential {
        NicknameCredential::handleChangeCredentialEvent insteadof AbstractPasswordCredential;
    }

    /**
     * @var NicknamePassword
     */
    private $credential;

    public function getCredential(): NicknamePassword
    {
        return $this->credential;
    }
}
