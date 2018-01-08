<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Infra\Uuid\DomainId;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Doctrine\DBAL\Types\{ConversionException, Type};
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @todo support both UUID+default infra (i.e. auto generated integers)
 */
class DomainUuidType extends Type
{
    public function getName(): string
    {
        return str_replace('\\', '_', get_class($this));
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($fieldDeclaration);
    }

    final public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof DomainId) {
            return $value->toString();
        }

        throw ConversionException::conversionFailed($value, $this->getName());
    }

    final public function convertToPHPValue($value, AbstractPlatform $platform): ?DomainIdInterface
    {
        if (null === $value) {
            return null;
        }

        if (is_string($value)) {
            try {
                return $this->convertToDomainId($value);
            } catch (InvalidUuidStringException $e) {
            }
        }

        throw ConversionException::conversionFailed($value, $this->getName());
    }

    protected function convertToDomainId(string $value): DomainIdInterface
    {
        return new DomainId($value);
    }
}
