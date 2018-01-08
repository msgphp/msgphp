<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

use MsgPhp\Domain\DomainIdInterface;

abstract class BaseTestEntity
{
    /**
     * @return $this
     */
    final public static function create(array $fields = []): self
    {
        /** @var $this $entity */
        $entity = new static();

        foreach ($fields as $field => $value) {
            $entity->$field = $value;
        }

        return $entity;
    }

    final public static function getPrimaryIds(self $entity, &$primitives = null): array
    {
        $ids = $primitives = [];

        foreach ($entity::getIdFields() as $field) {
            $ids[] = $entity->$field;

            if ($entity->$field instanceof  DomainIdInterface) {
                $primitives[] = $entity->$field->isEmpty() ? null : $entity->$field->toString();
            } elseif ($entity->$field instanceof self) {
                self::getPrimaryIds($entity->$field, $nestedPrimitives);

                $primitives[] = $nestedPrimitives;
            } else {
                $primitives[] = $entity->$field;
            }
        }

        return $ids;
    }

    final public static function createEntities(): iterable
    {
        foreach (self::getFields() as $fields) {
            yield self::create($fields);
        }
    }

    final public static function getFields(): iterable
    {
        $fieldNames = array_keys($fieldValues = static::getFieldValues());
        $cartesian = function (array $set) use (&$cartesian): array {
            if (!$set) {
                return [[]];
            }

            $subset = array_shift($set);
            $cartesianSubset = $cartesian($set);
            $result = array();
            foreach ($subset as $value) {
                foreach ($cartesianSubset as $p) {
                    array_unshift($p, $value);
                    $result[] = $p;
                }
            }

            return $result;
        };

        foreach ($cartesian($fieldValues) as $fieldValues) {
            yield array_combine($fieldNames, $fieldValues);
        }
    }

    abstract public static function getIdFields(): array;

    abstract public static function getFieldValues(): array;
}
