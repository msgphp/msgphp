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
    private $passwordResetToken;
    private $passwordRequestedAt;

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

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->passwordRequestedAt;
    }

    public function changeEmail(string $email): void
    {
        $this->email = $email;
    }

    public function changePassword(string $password): void
    {
        $this->password = $password;
        $this->passwordResetToken = null;
        $this->passwordRequestedAt = null;
    }

    public function requestPassword(): void
    {
        $this->passwordResetToken = bin2hex(random_bytes(32));
        $this->passwordRequestedAt = new \DateTimeImmutable();
    }
}
