<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures;

use MsgPhp\Domain\DomainCollectionInterface;

interface DomainEntityRepositoryTraitInterface
{
    public function doFindAll(int $offset = 0, int $limit = 0): DomainCollectionInterface;

    public function doFindAllByFields(array $fields, int $offset = 0, int $limit = 0): DomainCollectionInterface;

    public function doFind($id, ...$idN);

    public function doFindByFields(array $fields);

    public function doExists($id, ...$idN): bool;

    public function doExistsByFields(array $fields): bool;

    public function doSave($entity): void;

    public function doDelete($entity): void;
}
