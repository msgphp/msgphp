<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Security;

use MsgPhp\User\Credential\PasswordProtectedCredential;
use MsgPhp\User\Password\PasswordAlgorithm;
use MsgPhp\User\User;
use MsgPhp\User\UserId;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserIdentity implements UserInterface, EquatableInterface
{
    /**
     * @var UserId
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
     * @var PasswordAlgorithm|null
     */
    private $passwordAlgorithm;

    /**
     * @param string[] $roles
     */
    public function __construct(User $user, string $originUsername = null, array $roles = [])
    {
        $this->id = $user->getId();

        if ($this->id->isEmpty()) {
            throw new \LogicException('The user ID cannot be empty.');
        }

        $this->originUsername = $originUsername;
        $this->roles = $roles;

        $credential = $user->getCredential();

        if ($credential instanceof PasswordProtectedCredential) {
            $this->password = $credential->getPassword();
            $this->passwordAlgorithm = $credential->getPasswordAlgorithm();
        }
    }

    public function getUserId(): UserId
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

    public function getPasswordAlgorithm(): ?PasswordAlgorithm
    {
        return $this->passwordAlgorithm;
    }

    public function getSalt(): ?string
    {
        return $this->passwordAlgorithm->salt->token ?? null;
    }

    public function eraseCredentials(): void
    {
        $this->password = $this->passwordAlgorithm = null;
    }

    public function isEqualTo(UserInterface $user)
    {
        return $user instanceof self && $user->getUserId()->equals($this->id);
    }
}
