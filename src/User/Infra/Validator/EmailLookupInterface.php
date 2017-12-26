<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Validator;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface EmailLookupInterface
{
    public function exists(string $email): bool;

    public function existsPrimary(string $email): bool;
}
