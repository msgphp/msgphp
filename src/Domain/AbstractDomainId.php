<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class AbstractDomainId implements DomainId
{
    /** @var DomainId */
    protected $id;

    public function __construct(?DomainId $id = null)
    {
        $this->id = $id ?? new GenericDomainId();
    }

    public function __toString(): string
    {
        return $this->id->toString();
    }

    /**
     * @return static
     */
    public static function fromInt(?int $value): self
    {
        return new static(GenericDomainId::fromInt($value));
    }

    public function isNil(): bool
    {
        return $this->id->isNil();
    }

    public function equals($other): bool
    {
        return $other instanceof static && $this->id->equals($other->id);
    }

    public function toString(): string
    {
        return $this->id->toString();
    }
}
