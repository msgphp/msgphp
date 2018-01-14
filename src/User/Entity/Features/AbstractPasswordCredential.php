<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\Entity\Credential\Features\PasswordProtected;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
trait AbstractPasswordCredential
{
    use AbstractCredential;

    /** @var PasswordProtected */
    private $credential;

    public function getPassword(): string
    {
        return $this->credential->getPassword();
    }

    public function changePassword(string $password): void
    {
        $this->credential = $this->credential->withPassword($password);
    }
}
