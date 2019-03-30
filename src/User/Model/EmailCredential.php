<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Credential\Email;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EmailCredential
{
    use AbstractCredential;

    /**
     * @var Email
     */
    private $credential;

    public function getEmail(): string
    {
        return $this->credential->getUsername();
    }
}
