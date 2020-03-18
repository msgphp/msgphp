<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class GenericDomainId implements DomainId
{
    /** @var string */
    private $id;

    public function __construct(?string $id = null)
    {
        $this->id = $id ?? '';
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public static function fromInt(?int $value): self
    {
        return new self(0 === ($value ?? 0) ? null : (string) $value);
    }

    public function isNil(): bool
    {
        return '' === $this->id;
    }

    public function equals($other): bool
    {
        return $other instanceof DomainId && $this->id === $other->toString();
    }

    public function toString(): string
    {
        return $this->id;
    }
}
