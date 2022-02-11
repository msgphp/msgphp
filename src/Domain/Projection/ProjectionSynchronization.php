<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

use Psr\Log\LoggerInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ProjectionSynchronization
{
    private $typeRegistry;
    private $repository;

    /** @var iterable<string, array> */
    private $documentProvider;
    private $logger;

    /**
     * @param iterable<string, array> $documentProvider
     */
    public function __construct(ProjectionTypeRegistry $typeRegistry, ProjectionRepository $repository, iterable $documentProvider, ?LoggerInterface $logger = null)
    {
        $this->typeRegistry = $typeRegistry;
        $this->repository = $repository;
        $this->documentProvider = $documentProvider;
        $this->logger = $logger;
    }

    public function synchronize(): int
    {
        $this->typeRegistry->destroy();
        $this->typeRegistry->initialize();

        $grouped = [];
        $synchronized = 0;

        foreach ($this->documentProvider as $type => $document) {
            $grouped[$type][] = $document;
            ++$synchronized;
        }
        foreach ($grouped as $type => $documents) {
            $this->repository->saveAll($type, $documents);
        }

        return $synchronized;
    }
}
