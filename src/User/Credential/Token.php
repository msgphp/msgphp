<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Event\Domain\ChangeCredential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Token implements Credential
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
        if ($tokenChanged = ($this->token !== $token = $event->getStringField('token'))) {
            $this->token = $token;
        }

        return $tokenChanged;
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
