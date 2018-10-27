<?php

declare(strict_types=1);

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
final class SecurityUser implements UserInterface, EquatableInterface, \Serializable
{
    private $id;
    private $originUsername;
    private $roles;
    private $password;
    private $passwordSalt;

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

    public function serialize(): string
    {
        return serialize([$this->id, $this->originUsername, $this->roles, $this->password, $this->passwordSalt]);
    }

    public function unserialize($serialized): void
    {
        list($this->id, $this->originUsername, $this->roles, $this->password, $this->passwordSalt) = unserialize($serialized);
    }
}
