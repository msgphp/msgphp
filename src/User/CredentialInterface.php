<?php

declare(strict_types=1);

namespace MsgPhp\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface CredentialInterface
{
    public static function getUsernameField(): string;

    public function getUsername(): string;
}
