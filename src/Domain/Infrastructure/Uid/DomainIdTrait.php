<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Uid;

use MsgPhp\Domain\DomainId;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait DomainIdTrait
{
    /** @var UuidInterface */
    private $uuid;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    /**
     * @internal
     */
    public function __toString(): string
    {
        return $this->uuid->toString();
    }

    /**
     * @param mixed $value
     *
     * @return static
     */
    public static function fromValue($value): DomainId
    {
        if (null === $value || $value instanceof UuidInterface) {
            return new static($value);
        }
        if (\is_string($value)) {
            return new static(Uuid::fromString($value));
        }

        throw new \LogicException('Raw UUID value must be of type string, got "'.\gettype($value).'".');
    }

    public function isNil(): bool
    {
        return $this->uuid->equals(Uuid::fromString(Uuid::NIL));
    }

    /**
     * @param mixed $other
     */
    public function equals($other): bool
    {
        if (!$other instanceof self || static::class !== \get_class($other)) {
            return false;
        }

        return $this->uuid->equals($other->uuid);
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }
}
