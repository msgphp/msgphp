<?php

declare(strict_types=1);

/*
 * This file is part of the MsgPHP package.
 *
 * (c) Roland Franssen <franssen.roland@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MsgPhp\User\Infra\Security;

use MsgPhp\User\Entity\Credential\Anonymous;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Password\PasswordProtectedInterface;
use MsgPhp\User\UserIdInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SecurityUser implements UserInterface, EquatableInterface
{
    /**
     * @var UserIdInterface
     */
    private $id;

    /**
     * @var string|null
     */
    private $originUsername;

    /**
     * @var string[]
     */
    private $roles;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var string|null
     */
    private $passwordSalt;

    /**
     * @param string[] $roles
     */
    public function __construct(User $user, string $originUsername = null, array $roles = [])
    {
        $this->id = $user->getId();

        if ($this->id->isEmpty()) {
            throw new \LogicException('The user ID cannot be empty.');
        }

        $credential = $user->getCredential();

        if (null === $originUsername && !$credential instanceof Anonymous) {
            $originUsername = $credential->getUsername();
        }

        $this->originUsername = $originUsername;
        $this->roles = $roles;

        if ($credential instanceof PasswordProtectedInterface) {
            $this->password = $credential->getPassword();
            $this->passwordSalt = $credential->getPasswordAlgorithm()->salt->token ?? null;
        }
    }

    public function getUserId(): UserIdInterface
    {
        return $this->id;
    }

    public function getOriginUsername(): ?string
    {
        return $this->originUsername;
    }

    public function getUsername(): string
    {
        return $this->id->toString();
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return $this->password ?? '';
    }

    public function getSalt(): ?string
    {
        return $this->passwordSalt;
    }

    public function eraseCredentials(): void
    {
        $this->password = $this->passwordSalt = null;
    }

    public function isEqualTo(UserInterface $user)
    {
        return $user instanceof self && $user->getUserId()->equals($this->id);
    }
}
