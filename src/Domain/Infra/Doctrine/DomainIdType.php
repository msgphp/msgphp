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

    final public static function setClass(string $class): void
    {
        if (!is_subclass_of($class, DomainIdInterface::class)) {
            throw new \LogicException(sprintf('Domain ID class must be a sub class of "%s", got "%s".', DomainIdInterface::class, $class));
        }

        self::$mapping[static::class]['class'] = $class;
    }

    final public static function getClass(): string
    {
        return self::$mapping[static::class]['class'] ?? DomainId::class;
    }

    final public static function setDataType(string $type): void
    {
        self::$mapping[static::class]['data_type'] = $type;
    }

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
            $class = \get_class($value);

            foreach (self::$mapping as $type => $mapping) {
                if ($class === $mapping['class']) {
                    return $type::NAME;
                }
            }

            return self::NAME;
        }

        return null;
    }

    /**
     * @internal
     */
    final public static function resolveValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof DomainIdInterface) {
            $class = \get_class($value);
            $type = Type::INTEGER;

            foreach (self::$mapping as $type => $mapping) {
                if ($class === $mapping['class']) {
                    $type = $mapping['data_type'];
                    break;
                }
            }

            return self::getType($type)->convertToPHPValue($value->isEmpty() ? null : $value->toString(), $platform);
        }

        return $value;
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

        /** @psalm-suppress InvalidStringClass */
        return null === $value ? null : static::getClass()::fromValue($value);
    }

    final protected static function getInnerType(): Type
    {
        return self::getType(static::getDataType());
    }
}
