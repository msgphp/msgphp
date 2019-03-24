<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

use MsgPhp\Domain\PaginatedDomainCollection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface ProjectionRepository
{
    /**
     * @return PaginatedDomainCollection|ProjectionDocument[]
     */
    public function findAll(string $type, int $offset = 0, int $limit = 0): PaginatedDomainCollection;

    public function find(string $type, string $id): ?ProjectionDocument;

    public function clear(string $type): void;

    public function save(ProjectionDocument $document): void;

    public function delete(string $type, string $id): void;
}
