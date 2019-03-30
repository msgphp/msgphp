<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

/**
 * Represents a user credential that is bound to a known username.
 *
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UsernameCredential extends Credential
{
    public static function getUsernameField(): string;

    public function getUsername(): string;
}
