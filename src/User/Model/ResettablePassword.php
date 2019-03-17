<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Event\Domain\RequestPasswordEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait ResettablePassword
{
    /**
     * @var string|null
     */
    private $passwordResetToken;

    /**
     * @var \DateTimeInterface|null
     */
    private $passwordRequestedAt;

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->passwordRequestedAt;
    }

    public function requestPassword(string $token = null): void
    {
        $this->passwordResetToken = $token ?? bin2hex(random_bytes(32));
        $this->passwordRequestedAt = new \DateTimeImmutable();
    }

    public function clearPasswordRequest(): void
    {
        $this->passwordResetToken = null;
        $this->passwordRequestedAt = null;
    }

    private function handleRequestPasswordEvent(RequestPasswordEvent $event): bool
    {
        if (null === $event->token || $event->token !== $this->passwordResetToken) {
            $this->requestPassword($event->token);

            return true;
        }

        return false;
    }
}
