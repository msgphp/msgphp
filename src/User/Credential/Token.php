<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Event\Domain\ChangeCredential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Token implements UsernameCredential
{
    /**
     * @var string
     */
    private $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function __invoke(ChangeCredential $event): bool
    {
        [
            'token' => $this->token,
        ] = $event->fields + $vars = get_object_vars($this);

        return $vars !== get_object_vars($this);
    }

    public static function getUsernameField(): string
    {
        return 'token';
    }

    public function getUsername(): string
    {
        return $this->token;
    }
}
