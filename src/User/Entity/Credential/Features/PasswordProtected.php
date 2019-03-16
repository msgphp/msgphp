<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Credential\Features;

use MsgPhp\User\Password\PasswordAlgorithm;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait PasswordProtected
{
    /**
     * @var string
     */
    private $password;

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPasswordAlgorithm(): ?PasswordAlgorithm
    {
        return null;
    }

    abstract public function withPassword(string $password): self;
}
