<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Event\Domain\ChangeCredential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Anonymous implements Credential
{
    public function __invoke(ChangeCredential $event): bool
    {
        throw new \BadMethodCallException('An anonymous credential cannot be changed.');
    }
}
