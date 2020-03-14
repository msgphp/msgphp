<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

use MsgPhp\Domain\DomainId;

abstract class BaseTestEntity
{
    /**
     * @return static
     */
    final public static function create(array $fields = []): self
    {
        $entity = new static();

        foreach ($fields as $field => $value) {
            if (method_exists($entity, $method = 'set'.ucfirst($field))) {
                $entity->{$method}($value);
            } else {
                $entity->{$field} = $value;
            }
        }

        return $entity;
    }

    /**
     * @return array<string, mixed>
     */
    final public static function getPrimaryIds(self $entity, array &$primitives = []): array
    {
        $ids = $primitives = [];

        foreach ($entity::getIdFields() as $field) {
            $ids[$field] = $id = method_exists($entity, $method = 'get'.ucfirst($field)) ? $entity->{$method}() : $entity->{$field};

            if ($id instanceof DomainId) {
                $primitives[$field] = $id->isNil() ? null : $id->toString();
            } elseif ($id instanceof self) {
                $nestedPrimitiveIds = [];
                self::getPrimaryIds($id, $nestedPrimitiveIds);

                $primitives[$field] = $nestedPrimitiveIds;
            } else {
                $primitives[$field] = $id;
            }
        }

        return $ids;
    }

    /**
     * @return iterable<int, self>
     */
    final public static function createEntities(): iterable
    {
        foreach (self::getFields() as $fields) {
            yield self::create($fields);
        }
    }

    /**
     * @return iterable<int, array<string, mixed>>
     */
    final public static function getFields(): iterable
    {
        $fieldNames = array_keys($fieldValues = static::getFieldValues());
        $cartesian = static function (array $set) use (&$cartesian): array {
            if (!$set) {
                return [[]];
            }

            $subset = array_shift($set);
            $cartesianSubset = $cartesian($set);
            $result = [];
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

    /**
     * @return array<int, string>
     */
    abstract public static function getIdFields(): array;

    /**
     * @return array<string, mixed>
     */
    abstract public static function getFieldValues(): array;
}
