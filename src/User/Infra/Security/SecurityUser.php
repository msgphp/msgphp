<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\User\Entity\User;
use MsgPhp\User\Password\PasswordProtectedInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SecurityUser implements UserInterface, EquatableInterface, \Serializable
{
    private $id;
    private $roles;
    private $password;
    private $passwordSalt;

    public function __construct(User $user, array $roles = [])
    {
        $this->id = $user->getId()->toString();
        $this->roles = $roles;

        $credential = $user->getCredential();
        if ($credential instanceof PasswordProtectedInterface) {
            $this->password = $credential->getPassword();

            $algorithm = $credential->getPasswordAlgorithm();
            if (null !== $algorithm->salt) {
                $this->passwordSalt = $algorithm->salt->token;
            }
        }
    }

    public function getUsername(): string
    {
        return $this->id;
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
        $this->password = '';
        $this->passwordSalt = null;
    }

    public function isEqualTo(UserInterface $user)
    {
        return $user instanceof self && $this->id === $user->getUsername();
    }

    public function serialize(): string
    {
        return serialize([$this->id, $this->roles, $this->password, $this->passwordSalt]);
    }

    public function unserialize($serialized): void
    {
        list($this->id, $this->roles, $this->password, $this->passwordSalt) = unserialize($serialized);
    }
}
