<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Credential\EmailPassword;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EmailPasswordCredential
{
    use EmailCredential;

    /**
     * @var EmailPassword
     */
    private $credential;

    public function getPassword(): string
    {
        return $this->credential->getPassword();
    }
}
