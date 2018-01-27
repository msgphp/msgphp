<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Credential;

use MsgPhp\User\CredentialInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Anonymous implements CredentialInterface
{
    public static function getUsernameField(): string
    {
        return 'username';
    }

    public function getUsername(): string
    {
        return 'anonymous_'.bin2hex(random_bytes(8));
    }
}
