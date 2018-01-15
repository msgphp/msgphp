<?php

declare(strict_types=1);

namespace MsgPhp\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface CredentialInterface
{
    public function getUsername(): string;
}
