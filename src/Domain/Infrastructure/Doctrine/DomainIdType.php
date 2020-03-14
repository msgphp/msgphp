<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use MsgPhp\Domain\DomainId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class DomainIdType extends Type
{
    public const NAME = 'msgphp_domain_id';

    /** @var array<class-string<DomainIdType>, array> */
    private static $mapping = [];

    /**
     * @param class-string<DomainId> $class
     */
    final public static function setClass(string $class): void
    {
        if (!is_subclass_of($class, DomainId::class)) {
            throw new \LogicException('Domain ID class must be a sub class of "'.DomainId::class.'", got "'.$class.'".');
        }

        self::$mapping[static::class]['class'] = $class;
    }

    /**
     * @return class-string<DomainId>
     */
    final public static function getClass(): string
    {
        if (!isset(self::$mapping[static::class]['class'])) {
            throw new \LogicException('No class set for type "'.static::class.'".');
        }

        return self::$mapping[static::class]['class'];
    }

    final public static function setDataType(string $type): void
    {
        self::$mapping[static::class]['data_type'] = $type;
    }

    final public static function getDataType(): string
    {
        /** @psalm-suppress DeprecatedConstant */
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
     * @param mixed $value
     *
     * @internal
     */
    final public static function resolveName($value): ?string
    {
        if ($value instanceof DomainId) {
            $class = \get_class($value);

            foreach (self::$mapping as $type => $mapping) {
                if (($mapping['class'] ?? null) === $class) {
                    return $type::NAME;
                }
            }

            return self::NAME;
        }

        return null;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     *
     * @internal
     */
    final public static function resolveValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof DomainId) {
            $class = \get_class($value);
            /** @psalm-suppress DeprecatedConstant */
            $type = Type::INTEGER;

            foreach (self::$mapping as $mapping) {
                if (($mapping['class'] ?? null) === $class) {
                    $type = $mapping['data_type'] ?? $type;

                    break;
                }
            }

            return self::getType($type)->convertToPHPValue($value->isNil() ? null : $value->toString(), $platform);
        }

        return $value;
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return static::getInnerType()->getSQLDeclaration($fieldDeclaration, $platform);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof DomainId) {
            $value = $value->isNil() ? null : $value->toString();
        }

        try {
            return static::getInnerType()->convertToDatabaseValue($value, $platform);
        } catch (ConversionException $e) {
            throw ConversionException::conversionFailed($value ?? '', $this->getName());
        }
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?DomainId
    {
        try {
            $value = static::getInnerType()->convertToPHPValue($value, $platform);
        } catch (ConversionException $e) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return null === $value ? null : static::getClass()::fromValue($value);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    final protected static function getInnerType(): Type
    {
        return self::getType(static::getDataType());
    }
}
