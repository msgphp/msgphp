<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use MsgPhp\Domain\{DomainId, DomainIdInterface};
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DomainIdType extends Type
{
    public const NAME = 'msgphp_domain_id';

    private static $mapping = [];

    /**
     * @internal
     */
    final public static function setClass(string $class): void
    {
        if (!is_subclass_of($class, DomainIdInterface::class)) {
            throw new \LogicException(sprintf('Domain ID class must be a sub class of "%s", got "%s".', DomainIdInterface::class, $class));
        }

        self::$mapping[static::class]['class'] = $class;
    }

    /**
     * @internal
     */
    final public static function getClass(): string
    {
        return self::$mapping[static::class]['class'] ?? DomainId::class;
    }

    /**
     * @internal
     */
    final public static function setDataType(string $dataType): void
    {
        self::$mapping[static::class]['data_type'] = $dataType;
    }

    /**
     * @internal
     */
    final public static function getDataType(): string
    {
        return self::$mapping[static::class]['data_type'] ?? Type::INTEGER;
    }

    /**
     * @internal
     */
    final public static function resetMapping(): void
    {
        self::$mapping = [];
    }

    /**
     * @internal
     */
    final public static function resolveName($value): ?string
    {
        if ($value instanceof DomainIdInterface) {
            $class = get_class($value);

            foreach (self::$mapping as $type => $mapping) {
                if ($class === $mapping['class']) {
                    return $type::NAME;
                }
            }
        }

        return null;
    }

    final public function getName(): string
    {
        return static::NAME;
    }

    final public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return static::getInnerType()->getSQLDeclaration($fieldDeclaration, $platform);
    }

    final public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof DomainIdInterface) {
            $value = $value->isEmpty() ? null : $value->toString();
        }

        try {
            return static::getInnerType()->convertToDatabaseValue($value, $platform);
        } catch (ConversionException $e) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }
    }

    final public function convertToPHPValue($value, AbstractPlatform $platform): ?DomainIdInterface
    {
        try {
            $value = static::getInnerType()->convertToPHPValue($value, $platform);
        } catch (ConversionException $e) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        if (null === $value) {
            return null;
        }

        return static::getClass()::fromValue($value);
    }

    final protected static function getInnerType(): Type
    {
        return self::getType(static::getDataType());
    }
}
