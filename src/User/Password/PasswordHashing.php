<?php

declare(strict_types=1);

namespace MsgPhp\User\Password;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface PasswordHashing
{
    public function hash(string $plainPassword, PasswordAlgorithm $algorithm = null): string;

    public function isValid(string $hashedPassword, string $plainPassword, PasswordAlgorithm $algorithm = null): bool;
}
