<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @template T of object
 */
final class ProjectionDocumentProvider implements \IteratorAggregate
{
    /** @var iterable<int, callable():T> */
    private $dataProviders;

    /** @var callable(T):array */
    private $transformer;

    /** @var callable(T):string */
    private $typeResolver;

    /**
     * @param iterable<int, callable():T> $dataProviders
     * @param callable(T):array           $transformer
     * @param callable(T):string          $typeResolver
     */
    public function __construct(iterable $dataProviders, callable $transformer, callable $typeResolver)
    {
        $this->dataProviders = $dataProviders;
        $this->transformer = $transformer;
        $this->typeResolver = $typeResolver;
    }

    /**
     * @return \Traversable<string, array>
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->dataProviders as $dataProvider) {
            foreach ($dataProvider() as $object) {
                yield ($this->typeResolver)($object) => ($this->transformer)($object);
            }
        }
    }
}
