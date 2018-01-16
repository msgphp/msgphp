<?php

declare(strict_types=1);

namespace MsgPhp\User\Password;

/**
 * Represents a password protected resource. The password is usually a *hashed* value (thus secret).
 *
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface PasswordProtectedInterface
{
    public function getPassword(): string;

    public function getPasswordAlgorithm(): PasswordAlgorithm;
}
