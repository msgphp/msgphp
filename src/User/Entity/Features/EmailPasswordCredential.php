<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\Entity\Credential\EmailPassword;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EmailPasswordCredential
{
    use AbstractPasswordCredential;
    use EmailCredential;

    /** @var EmailPassword */
    private $credential;

    public function getCredential(): EmailPassword
    {
        return $this->credential;
    }
}
