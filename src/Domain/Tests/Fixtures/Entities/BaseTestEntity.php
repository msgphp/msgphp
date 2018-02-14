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
            if (method_exists($entity, $method = 'set'.ucfirst($field))) {
                $entity->$method($value);
            } else {
                $entity->$field = $value;
            }
        }

        return $entity;
    }

    final public static function getPrimaryIds(self $entity, &$primitives = null): array
    {
        $ids = $primitives = [];

        foreach ($entity::getIdFields() as $field) {
            $ids[$field] = $id = method_exists($entity, $method = 'get'.ucfirst($field)) ? $entity->$method() : $entity->$field;

            if ($id instanceof DomainIdInterface) {
                $primitives[$field] = $id->isEmpty() ? null : $id->toString();
            } elseif ($id instanceof self) {
                self::getPrimaryIds($id, $nestedPrimitives);

                $primitives[$field] = $nestedPrimitives;
            } else {
                $primitives[$field] = $id;
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
