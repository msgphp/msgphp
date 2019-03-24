<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Credential\Email;
use MsgPhp\User\Event\Domain\ChangeCredential;

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

    public function getCredential(): Email
    {
        return $this->credential;
    }

    public function getEmail(): string
    {
        return $this->credential->getUsername();
    }

    public function changeEmail(string $email): void
    {
        ($this->credential)(new ChangeCredential([Email::getUsernameField() => $email]));
    }
}
