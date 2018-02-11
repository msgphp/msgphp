<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\IntegerType;
use MsgPhp\Domain\{DomainId, DomainIdInterface};

final class DoctrineDomainIdType extends IntegerType
{
    public function getName()
    {
        return 'domain_id';
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof DomainIdInterface) {
            return $value->isEmpty() ? null : (int) $value->toString();
        }

        throw ConversionException::conversionFailed($value, $this->getName());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?DomainIdInterface
    {
        if (null === $value) {
            return null;
        }

        if (is_scalar($value)) {
            return new DomainId((string) $value);
        }

        throw ConversionException::conversionFailed($value, $this->getName());
    }
}
