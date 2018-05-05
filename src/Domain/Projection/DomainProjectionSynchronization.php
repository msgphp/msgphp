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
    private $documentProvider;

    /**
     * @param DomainProjectionDocument[] $documentProvider
     */
    public function __construct(DomainProjectionTypeRegistryInterface $typeRegistry, DomainProjectionRepositoryInterface $repository, iterable $documentProvider)
    {
        $this->typeRegistry = $typeRegistry;
        $this->repository = $repository;
        $this->documentProvider = $documentProvider;
    }

    /**
     * @return DomainProjectionDocument[]
     */
    public function synchronize(): iterable
    {
        foreach ($this->typeRegistry->all() as $type) {
            $this->repository->clear($type);
        }

        foreach ($this->documentProvider as $document) {
            if (DomainProjectionDocument::STATUS_UNKNOWN !== $document->status) {
                continue;
            }

            try {
                $document->status = DomainProjectionDocument::STATUS_SYNCHRONIZED;

                $this->repository->save($document);
            } catch (\Throwable $e) {
                $document->status = DomainProjectionDocument::STATUS_FAILED_SAVING;
                $document->error = $e;
            }

            yield $document;
        }
    }
}
