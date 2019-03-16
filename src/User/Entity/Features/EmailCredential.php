<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\Entity\Credential\Email;
use MsgPhp\User\Event\Domain\ChangeCredentialEvent;

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
        ($this->credential)(new ChangeCredentialEvent([Email::getUsernameField() => $email]));
    }
}
