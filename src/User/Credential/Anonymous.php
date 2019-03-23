<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Event\Domain\ChangeCredentialEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Anonymous implements CredentialInterface
{
    public function __invoke(ChangeCredentialEvent $event): bool
    {
        throw new \BadMethodCallException('An anonymous credential cannot be changed.');
    }

    public static function getUsernameField(): string
    {
        throw new \BadMethodCallException('An anonymous credential has no username field.');
    }

    public function getUsername(): string
    {
        throw new \BadMethodCallException('An anonymous credential has no username.');
    }
}
