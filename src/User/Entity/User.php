<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity;

use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class User
{
    private $id;
    private $email;
    private $password;

    public function __construct(UserIdInterface $id, string $email, string $password)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
    }

    public function getId(): UserIdInterface
    {
        return $this->id;
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
