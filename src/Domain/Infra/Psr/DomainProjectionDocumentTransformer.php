<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Psr;

use MsgPhp\Domain\Projection\{DomainProjectionDocument, DomainProjectionDocumentTransformerInterface};
use Psr\Container\ContainerInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainProjectionDocumentTransformer implements DomainProjectionDocumentTransformerInterface
{
    private $transformers;

    public function __construct(ContainerInterface $transformers)
    {
        $this->transformers = $transformers;
    }

    public function transform($object): DomainProjectionDocument
    {
        if (!$this->transformers->has($class = get_class($object))) {
            throw new \LogicException(sprintf('No projection document transformer available for class "%s".', $class));
        }

        if (!is_callable($transformer = $this->transformers->get($class))) {
            throw new \LogicException(sprintf('Projection document transformer for class "%s" must be a callable, got "%s".', $class, gettype($transformer)));
        }

        $document = $transformer($object);

        if (!$document instanceof DomainProjectionDocument) {
            throw new \LogicException(sprintf('Projection document transformer for class "%s" must return an instance of "%s", got "%s".', $class, DomainProjectionDocument::class, is_object($document) ? get_class($document) : gettype($document)));
        }

        $document->source = $object;

        return $document;
    }
}
