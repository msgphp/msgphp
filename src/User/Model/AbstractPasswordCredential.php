<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Event\Domain\ChangeCredentialEvent;
use MsgPhp\User\Password\PasswordProtectedInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
trait AbstractPasswordCredential
{
    use AbstractCredential;

    /**
     * @var CredentialInterface&PasswordProtectedInterface
     */
    private $credential;

    public function getPassword(): string
    {
        return $this->credential->getPassword();
    }

    public function changePassword(string $password): void
    {
        ($this->credential)(new ChangeCredentialEvent(['password' => $password]));
    }
}
