<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\InMemory;

final class ObjectIdentityMap
{
    private static $globalDefaultInstance;

    private $storage;

    public static function getGlobalDefault(): self
    {
        return self::$globalDefaultInstance ?? (self::$globalDefaultInstance = new self());
    }

    public function __construct()
    {
        $this->storage = new \SplObjectStorage();
    }

    public function all(string $class): iterable
    {
        foreach ($this->storage as $k => $object) {
            if (($stored = $this->storage->getInfo()) === $class || is_subclass_of($stored, $class)) {
                yield $object;
            }
        }
    }

    /**
     * @param object $object
     */
    public function contains($object): bool
    {
        return $this->storage->contains($object);
    }

    /**
     * @param object $object
     */
    public function persist($object): void
    {
        $this->storage->attach($object, \get_class($object));
    }

    /**
     * @param object $object
     */
    public function remove($object): void
    {
        $this->storage->detach($object);
    }
}
