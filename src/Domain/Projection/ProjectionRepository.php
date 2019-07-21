<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface ProjectionRepository
{
    public function find(string $type, string $id): ?array;

    public function save(string $type, array $document): void;

    /**
     * @param iterable<int, array> $documents
     */
    public function saveAll(string $type, iterable $documents): void;

    public function delete(string $type, string $id): bool;
}
