<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\ApiPlatform;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use MsgPhp\Domain\Projection\{DomainProjectionInterface, DomainProjectionRepositoryInterface, DomainProjectionTypeRegistryInterface};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainProjectionDataProvider implements CollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $typeRegistry;
    private $repository;

    public function __construct(DomainProjectionTypeRegistryInterface $typeRegistry, DomainProjectionRepositoryInterface $repository)
    {
        $this->typeRegistry = $typeRegistry;
        $this->repository = $repository;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return in_array($resourceClass, $this->typeRegistry->all(), true);
    }

    /**
     * @return DomainProjectionInterface[]
     */
    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        return $this->repository->findAll($resourceClass);
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?DomainProjectionInterface
    {
        return $this->repository->find($resourceClass, $id);
    }
}
