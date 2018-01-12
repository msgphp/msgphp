<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class PendingUser
{
    private $token;
    private $email;
    private $password;

    public function __construct(string $email, string $password)
    {
        $this->token = bin2hex(random_bytes(32));
        $this->email = $email;
        $this->password = $password;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
