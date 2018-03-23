<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainProjectionSynchronization
{
    private $typeRegistry;
    private $repository;
    private $providers;
    private $transformer;

    public function __construct(DomainProjectionTypeRegistryInterface $typeRegistry, DomainProjectionRepositoryInterface $repository, DomainProjectionDocumentTransformerInterface $transformer, iterable $providers)
    {
        $this->typeRegistry = $typeRegistry;
        $this->repository = $repository;
        $this->providers = $providers;
        $this->transformer = $transformer;
    }

    /**
     * @return DomainProjectionDocument[]
     */
    public function synchronize(): iterable
    {
        foreach ($this->typeRegistry->all() as $type) {
            $this->repository->clear($type);
        }

        foreach ($this->providers as $provider) {
            foreach ($provider() as $object) {
                try {
                    $document = $this->transformer->transform($object);
                } catch (\Exception $e) {
                    $document = new DomainProjectionDocument();
                    $document->status = DomainProjectionDocument::STATUS_FAILED_TRANSFORMATION;
                    $document->source = $object;
                    $document->error = $e;

                    yield $document;
                    continue;
                }

                try {
                    $document->status = DomainProjectionDocument::STATUS_VALID;

                    $this->repository->save($document);
                } catch (\Exception $e) {
                    $document->status = DomainProjectionDocument::STATUS_FAILED_SAVING;
                    $document->error = $e;
                } finally {
                    yield $document;
                }
            }
        }
    }
}
