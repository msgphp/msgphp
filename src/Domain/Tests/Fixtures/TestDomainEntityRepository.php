<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures;

use MsgPhp\Domain\DomainCollection;

interface TestDomainEntityRepository
{
    public function doFindAll(int $offset = 0, int $limit = 0): DomainCollection;

    public function doFindAllByFields(array $fields, int $offset = 0, int $limit = 0): DomainCollection;

    public function doFind($id);

    public function doFindByFields(array $fields);

    public function doExists($id): bool;

    public function doExistsByFields(array $fields): bool;

    public function doSave($entity): void;

    public function doDelete($entity): void;
}
