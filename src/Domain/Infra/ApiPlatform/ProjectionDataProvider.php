<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\ApiPlatform;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use MsgPhp\Domain\PaginatedDomainCollection;
use MsgPhp\Domain\Projection\ProjectionInterface;
use MsgPhp\Domain\Projection\ProjectionRepositoryInterface;
use MsgPhp\Domain\Projection\ProjectionTypeRegistryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ProjectionDataProvider implements CollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var ProjectionTypeRegistryInterface
     */
    private $typeRegistry;

    /**
     * @var ProjectionRepositoryInterface
     */
    private $repository;

    public function __construct(ProjectionTypeRegistryInterface $typeRegistry, ProjectionRepositoryInterface $repository)
    {
        $this->typeRegistry = $typeRegistry;
        $this->repository = $repository;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return \in_array($resourceClass, $this->typeRegistry->all(), true);
    }

    /**
     * @psalm-suppress ImplementedReturnTypeMismatch
     *
     * @return iterable|ProjectionInterface[]
     */
    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        $collection = $this->repository->findAll($resourceClass);

        return new Paginator(new PaginatedDomainCollection((function () use ($collection): iterable {
            foreach ($collection as $document) {
                yield $document->toProjection();
            }
        })(), $collection->getOffset(), $collection->getLimit(), (float) \count($collection), $collection->getTotalCount()));
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?ProjectionInterface
    {
        if (!is_scalar($id)) {
            throw new \InvalidArgumentException('Document ID must be a scalar.');
        }

        $document = $this->repository->find($resourceClass, (string) $id);

        return null === $document ? null : $document->toProjection();
    }
}
