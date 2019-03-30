<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Event\Domain\ChangeCredential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Email implements UsernameCredential
{
    use EmailAsUsername;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function __invoke(ChangeCredential $event): bool
    {
        [
            'email' => $this->email,
        ] = $event->fields + $vars = get_object_vars($this);

        return $vars !== get_object_vars($this);
    }
}
