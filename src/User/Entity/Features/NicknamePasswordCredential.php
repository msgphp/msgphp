<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\Entity\Credential\NicknamePassword;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait NicknamePasswordCredential
{
    use AbstractPasswordCredential;
    use NicknameCredential;

    /** @var NicknamePassword */
    private $credential;

    public function getCredential(): NicknamePassword
    {
        return $this->credential;
    }
}
