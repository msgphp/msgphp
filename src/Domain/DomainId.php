<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DomainId implements DomainIdInterface
{
    private $id;

    /**
     * @return static
     */
    final public static function fromValue($value): DomainIdInterface
    {
        if (null !== $value && !\is_string($value)) {
            $value = (string) $value;
        }

        return new static($value);
    }

    final public function __construct(string $id = null)
    {
        $this->id = $id;
    }

    final public function isEmpty(): bool
    {
        return null === $this->id;
    }

    final public function equals(DomainIdInterface $id): bool
    {
        return $id === $this ? true : (null !== $this->id && $id instanceof self && static::class === \get_class($id) ? $this->id === $id->id : false);
    }

    final public function toString(): string
    {
        return $this->id ?? '';
    }

    final public function __toString(): string
    {
        return $this->id ?? '';
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
