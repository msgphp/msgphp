<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Credential\PasswordProtectedCredentialInterface;
use MsgPhp\User\Event\Domain\ChangeCredentialEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait AbstractPasswordCredential
{
    use AbstractCredential;

    /**
     * @var PasswordProtectedCredentialInterface
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
