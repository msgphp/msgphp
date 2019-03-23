<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Event\Domain\ChangeCredentialEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EmailPassword implements PasswordProtectedCredentialInterface
{
    use EmailAsUsername;
    use PasswordProtection;

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
