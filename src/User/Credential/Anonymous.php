<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\CredentialInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Anonymous implements CredentialInterface
{
    public static function getUsernameField(): string
    {
        throw new \LogicException('An anonymous credential has no username field.');
    }

    public function getUsername(): string
    {
        throw new \LogicException('An anonymous credential has no username.');
    }
}
