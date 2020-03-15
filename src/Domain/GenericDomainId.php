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

    public static function fromValue($value): DomainId
    {
        if (null === $value || \is_string($value)) {
            return new self($value);
        }
        if (is_numeric($value)) {
            return new self((string) $value);
        }

        throw new \LogicException('Raw ID value must be of type string or number, got "'.\gettype($value).'".');
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
