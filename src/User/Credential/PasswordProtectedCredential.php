<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Password\PasswordAlgorithm;

/**
 * Represents a password protected credential. The password is usually a *hashed* value (thus secret).
 *
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface PasswordProtectedCredential extends Credential
{
    public function getPassword(): string;

    public function getPasswordAlgorithm(): ?PasswordAlgorithm;
}
