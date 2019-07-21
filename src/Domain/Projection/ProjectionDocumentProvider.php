<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ProjectionDocumentProvider implements \IteratorAggregate
{
    private $transformer;
    /** @var iterable<int, callable> */
    private $dataProviders;

    /**
     * @param iterable<int, callable> $dataProviders
     */
    public function __construct(ProjectionDocumentTransformer $transformer, iterable $dataProviders)
    {
        $this->transformer = $transformer;
        $this->dataProviders = $dataProviders;
    }

    /**
     * @return \Traversable<object, array>
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->dataProviders as $dataProvider) {
            foreach ($dataProvider() as $object) {
                yield $object => $this->transformer->transform($object);
            }
        }
    }
}
