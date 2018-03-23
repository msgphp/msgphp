<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DomainProjectionRepositoryInterface
{
    /**
     * @return DomainProjectionInterface[]
     */
    public function findAll(string $type, int $offset = 0, int $limit = 0): iterable;

    public function find(string $type, string $id): ?DomainProjectionInterface;

    public function clear(string $type): void;

    public function save(DomainProjectionDocument $document): void;

    public function delete(DomainProjectionDocument $document): void;
}
