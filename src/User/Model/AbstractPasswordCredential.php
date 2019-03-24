<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Credential\PasswordProtectedCredential;
use MsgPhp\User\Event\Domain\ChangeCredential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait AbstractPasswordCredential
{
    use AbstractCredential;

    /**
     * @var PasswordProtectedCredential
     */
    private $credential;

    public function getPassword(): string
    {
        return $this->credential->getPassword();
    }

    public function changePassword(string $password): void
    {
        ($this->credential)(new ChangeCredential(['password' => $password]));
    }
}
