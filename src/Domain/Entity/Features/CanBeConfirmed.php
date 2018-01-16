<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity\Features;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait CanBeConfirmed
{
    /** @var string|null */
    private $confirmationToken;

    /** @var \DateTimeInterface|null */
    private $confirmedAt;

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function getConfirmedAt(): ?\DateTimeInterface
    {
        return $this->confirmedAt;
    }

    public function isConfirmed(): bool
    {
        return null !== $this->confirmedAt;
    }

    public function confirm(): void
    {
        $this->confirmationToken = null;
        $this->confirmedAt = new \DateTimeImmutable();
    }
}
