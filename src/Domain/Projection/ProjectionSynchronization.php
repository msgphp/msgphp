<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ProjectionSynchronization
{
    private $typeRegistry;
    private $repository;
    private $documentProvider;

    /**
     * @param ProjectionDocument[] $documentProvider
     */
    public function __construct(ProjectionTypeRegistryInterface $typeRegistry, ProjectionRepositoryInterface $repository, iterable $documentProvider)
    {
        $this->typeRegistry = $typeRegistry;
        $this->repository = $repository;
        $this->documentProvider = $documentProvider;
    }

    /**
     * @return ProjectionDocument[]
     */
    public function synchronize(): iterable
    {
        foreach ($this->typeRegistry->all() as $type) {
            $this->repository->clear($type);
        }

        foreach ($this->documentProvider as $document) {
            if (ProjectionDocument::STATUS_UNKNOWN !== $document->status) {
                continue;
            }

            try {
                $document->status = ProjectionDocument::STATUS_SYNCHRONIZED;

                $this->repository->save($document);
            } catch (\Throwable $e) {
                $document->status = ProjectionDocument::STATUS_FAILED_SAVING;
                $document->error = $e;
            }

            yield $document;
        }
    }
}
