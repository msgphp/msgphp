<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Event\Domain\ChangeCredentialEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Email implements CredentialInterface
{
    use EmailAsUsername;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function __invoke(ChangeCredentialEvent $event): bool
    {
        if ($emailChanged = ($this->email !== $email = $event->getStringField('email'))) {
            $this->email = $email;
        }

        return $emailChanged;
    }
}
