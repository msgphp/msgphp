<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console;

use MsgPhp\User\Credential\UsernameCredential;
use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class UserDefinition
{
    public static function getDisplayName(User $user): string
    {
        $credential = $user->getCredential();

        if ($credential instanceof UsernameCredential) {
            return $credential->getUsername();
        }

        if (false !== ($i = strrpos($type = \get_class($credential), '\\'))) {
            $type = substr($type, $i + 1);
        }

        return lcfirst($type).'@'.$user->getId()->toString();
    }
}
