<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ProjectionDocumentProvider implements \IteratorAggregate
{
    /**
     * @var ProjectionDocumentTransformer
     */
    private $transformer;

    /**
     * @var iterable|callable[]
     */
    private $dataProviders;

    /**
     * @param iterable|callable[] $dataProviders
     */
    public function __construct(ProjectionDocumentTransformer $transformer, iterable $dataProviders)
    {
        $this->transformer = $transformer;
        $this->dataProviders = $dataProviders;
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->dataProviders as $dataProvider) {
            foreach ($dataProvider() as $object) {
                try {
                    $document = $this->transformer->transform($object);
                } catch (\Throwable $e) {
                    $document = new ProjectionDocument();
                    $document->status = ProjectionDocument::STATUS_FAILED_TRANSFORMATION;
                    $document->source = $object;
                    $document->error = $e;
                }

                yield $document;
            }
        }
    }
}
