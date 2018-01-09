<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\InMemory;

final class GlobalObjectMemory
{
    private static $defaultContext;

    private $storage;

    public static function createDefault(): self
    {
        return self::$defaultContext ?? (self::$defaultContext = new self());
    }

    public function __construct()
    {
        $this->storage = new \SplObjectStorage();
    }

    public function all(string $class): iterable
    {
        foreach ($this->storage as $k => $object) {
            if ($class === $this->storage->getInfo()) {
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
        $this->storage->attach($object, get_class($object));
    }

    /**
     * @param object $object
     */
    public function remove($object): void
    {
        $this->storage->detach($object);
    }
}
