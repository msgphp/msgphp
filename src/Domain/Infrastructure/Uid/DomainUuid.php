<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Uid;

use MsgPhp\Domain\DomainId;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainUuid implements DomainId
{
    /** @var UuidInterface */
    private $uuid;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString(): string
    {
        return $this->uuid->toString();
    }

    public static function fromString(?string $value): self
    {
        return new self(Uuid::fromString($value ?? Uuid::NIL));
    }

    public function isNil(): bool
    {
        return $this->uuid->equals(Uuid::fromString(Uuid::NIL));
    }

    public function equals($other): bool
    {
        return $other instanceof self && $this->uuid->equals($other->uuid);
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }
}
