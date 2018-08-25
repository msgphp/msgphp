<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\InMemory;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ObjectFieldAccessor
{
    private $accessor;

    public function __construct(PropertyAccessorInterface $accessor = null)
    {
        $this->accessor = $accessor;
    }

    /**
     * @param object $object
     */
    public function getValue($object, string $field)
    {
        if (null !== $this->accessor) {
            return $this->accessor->getValue($object, $field);
        }

        // @todo throw on dot/bracket notation (suggest sf/prop-access instead)

        if (method_exists($object, $method = 'get'.($uc = ucfirst($field))) || method_exists($object, $method = 'has'.$uc) || method_exists($object, $method = 'is'.$uc)) {
            return $object->$method();
        }

        if (method_exists($object, $field)) {
            return $object->$field();
        }

        if (property_exists($object, $field)) {
            return $object->$field;
        }

        throw new \RuntimeException(sprintf('Unknown field name "%s" for object "%s".', $field, \get_class($object)));
    }
}
