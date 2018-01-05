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
        $this->id = $id;
    }

    final public function isKnown(): bool
    {
        return null !== $this->id;
    }

    final public function equals(DomainIdInterface $id): bool
    {
        return null !== $this->id && $id instanceof self && get_class($id) === get_class($this) ? $this->id === $id->id : false;
    }

    final public function toString(): string
    {
        if (null === $this->id) {
            throw new \LogicException('An unknown domain ID cannot be casted to string.');
        }

        return $this->id;
    }

    final public function __toString(): string
    {
        return $this->toString();
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
        return $this->id;
    }
}
