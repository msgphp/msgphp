<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DomainId implements DomainIdInterface
{
    private $id;

    final public function __construct(string $id = null)
    {
        $this->id = $id ?? '';
    }

    final public function isEmpty(): bool
    {
        return '' === $this->id;
    }

    final public function equals(DomainIdInterface $id): bool
    {
        return '' !== $this->id && $id instanceof self && static::class === get_class($id) ? $this->id === $id->id : false;
    }

    final public function toString(): string
    {
        return $this->id;
    }

    final public function __toString(): string
    {
        return $this->id;
    }

    final public function serialize(): string
    {
        return serialize($this->id);
    }

    final public function unserialize($serialized): void
    {
        $this->id = unserialize($serialized);
    }

    final public function jsonSerialize(): ?string
    {
        return '' === $this->id ? null : $this->id;
    }
}