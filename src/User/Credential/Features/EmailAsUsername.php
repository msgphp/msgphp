<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential\Features;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EmailAsUsername
{
    /**
     * @var string
     */
    private $email;

    public static function getUsernameField(): string
    {
        return 'email';
    }

    public function getUsername(): string
    {
        return $this->email;
    }
}
