<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Security;

use MsgPhp\User\Credential\PasswordProtectedCredential;
use MsgPhp\User\User;
use MsgPhp\User\UserId;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserIdentity implements UserInterface, EquatableInterface, EncoderAwareInterface
{
    /** @var UserId */
    private $id;
    /** @var null|string */
    private $originUsername;
    /** @var array<int, string> */
    private $roles;
    /** @var null|string */
    private $password;
    /** @var string */
    private $hashing;

    /**
     * @param array<int, string> $roles
     */
    public function __construct(User $user, ?string $originUsername = null, array $roles = [], ?string $hashing = null)
    {
        $this->id = $user->getId();

        if ($this->id->isNil()) {
            throw new \LogicException('The user ID cannot be nil.');
        }

        $credential = $user->getCredential();

        if ($credential instanceof PasswordProtectedCredential) {
            $this->password = $credential->getPassword();
        }

        $this->originUsername = $originUsername;
        $this->roles = $roles;
        $this->hashing = $hashing ?? self::class;
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
     * @return array<int, string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        $this->password = null;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $user instanceof self && $user->getUserId()->equals($this->id);
    }

    public function getEncoderName(): string
    {
        return $this->hashing;
    }
}
