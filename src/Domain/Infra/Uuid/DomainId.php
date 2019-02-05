<?php

declare(strict_types=1);

/*
 * This file is part of the MsgPHP package.
 *
 * (c) Roland Franssen <franssen.roland@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MsgPhp\Domain\Infra\Uuid;

use MsgPhp\Domain\DomainIdInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DomainId implements DomainIdInterface, \JsonSerializable
{
    /**
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @inheritdoc
     *
     * @return static
     */
    final public static function fromValue($value): DomainIdInterface
    {
        if (null !== $value && !$value instanceof UuidInterface) {
            $value = Uuid::fromString((string) $value);
        }

        return new static($value);
    }

    final public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    final public function isEmpty(): bool
    {
        return false;
    }

    final public function equals(DomainIdInterface $id): bool
    {
        return $id === $this ? true : ($id instanceof self && static::class === \get_class($id) ? $this->uuid->equals($id->uuid) : false);
    }

    final public function toString(): string
    {
        return $this->uuid->toString();
    }

    final public function __toString(): string
    {
        return $this->uuid->toString();
    }

    /**
     * @return mixed
     */
    final public function jsonSerialize()
    {
        return $this->uuid->jsonSerialize();
    }
}
