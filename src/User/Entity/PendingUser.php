<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @todo figure out purpose; could be a User feature trait / reuse credential features
 */
class PendingUser
{
    private $email;
    private $password;
    private $token;

    public function __construct(string $email, string $password, string $token = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->token = $token ?? bin2hex(random_bytes(32));
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
