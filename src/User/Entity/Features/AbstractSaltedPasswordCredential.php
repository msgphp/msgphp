<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\Entity\Credential\Features\PasswordWithSaltProtected;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
trait AbstractSaltedPasswordCredential
{
    use AbstractPasswordCredential;

    /** @var PasswordWithSaltProtected */
    private $credential;

    public function getPasswordSalt(): string
    {
        return $this->credential->getPasswordSalt();
    }

    public function changePasswordSalt(string $passwordSalt): void
    {
        $this->credential = $this->credential->withPasswordSalt($passwordSalt);
    }
}
