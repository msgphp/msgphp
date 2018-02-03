<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\Entity\Credential\EmailSaltedPassword;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EmailSaltedPasswordCredential
{
    use AbstractSaltedPasswordCredential;
    use EmailCredential {
        EmailCredential::handleChangeCredentialEvent insteadof AbstractSaltedPasswordCredential;
    }

    /** @var EmailSaltedPassword */
    private $credential;

    public function getCredential(): EmailSaltedPassword
    {
        return $this->credential;
    }
}
