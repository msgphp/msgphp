<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait DomainIdTrait
{
    /** @var string */
    private $id;

    public function __construct(?string $id = null)
    {
        $this->id = $id ?? '';
    }

    /**
     * @internal
     */
    public function __toString(): string
    {
        return $this->id;
    }

    /**
     * @param mixed $value
     *
     * @return static
     */
    public static function fromValue($value): DomainId
    {
        if (null === $value || \is_string($value)) {
            return new static($value);
        }
        if (is_numeric($value)) {
            return new static((string) $value);
        }

        throw new \LogicException('Raw ID value must be of type string or number, got "'.\gettype($value).'".');
    }

    public function isNil(): bool
    {
        return '' === $this->id;
    }

    /**
     * @param mixed $other
     */
    public function equals($other): bool
    {
        if (!$other instanceof self || static::class !== \get_class($other)) {
            return false;
        }

        return $this->id === $other->id;
    }

    public function toString(): string
    {
        return $this->id;
    }
}
