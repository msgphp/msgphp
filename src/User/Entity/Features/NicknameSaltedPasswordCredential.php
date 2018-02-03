<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\Entity\Credential\NicknameSaltedPassword;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait NicknameSaltedPasswordCredential
{
    use AbstractSaltedPasswordCredential;
    use NicknameCredential {
        NicknameCredential::handleChangeCredentialEvent insteadof AbstractSaltedPasswordCredential;
    }

    /** @var NicknameSaltedPassword */
    private $credential;

    public function getCredential(): NicknameSaltedPassword
    {
        return $this->credential;
    }
}
