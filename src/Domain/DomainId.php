<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DomainId implements DomainIdInterface, \JsonSerializable
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @inheritdoc
     *
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
        if ('' === $id) {
            throw new \LogicException('ID must be a non empty string.');
        }

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

    /**
     * @return mixed
     */
    final public function jsonSerialize()
    {
        return $this->id;
    }
}
