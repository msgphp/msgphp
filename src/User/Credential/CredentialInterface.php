<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Event\Domain\ChangeCredentialEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface CredentialInterface
{
    public function __invoke(ChangeCredentialEvent $event): bool;

    public static function getUsernameField(): string;

    public function getUsername(): string;
}
