<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Credential;

use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Entity\Credential\Features\EmailAsUsername;
use MsgPhp\User\Entity\Credential\Features\PasswordProtected;
use MsgPhp\User\Event\Domain\ChangeCredentialEvent;
use MsgPhp\User\Password\PasswordProtectedInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EmailPassword implements CredentialInterface, PasswordProtectedInterface
{
    use EmailAsUsername;
    use PasswordProtected;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    public function __invoke(ChangeCredentialEvent $event): bool
    {
        if ($emailChanged = ($this->email !== $email = $event->getStringField('email'))) {
            $this->email = $email;
        }
        if ($passwordChanged = ($this->password !== $password = $event->getStringField('password'))) {
            $this->password = $password;
        }

        return $emailChanged || $passwordChanged;
    }
}
