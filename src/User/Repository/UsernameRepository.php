<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\User\Username;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UsernameRepository
{
    /**
     * @return DomainCollection|Username[]
     */
    public function lookup(): DomainCollection;

    /**
     * @return DomainCollection|Username[]
     */
    public function findAll(int $offset = 0, int $limit = 0): DomainCollection;

    public function find(string $username): Username;

    public function exists(string $username): bool;

    public function save(Username $username): void;

    public function delete(Username $username): void;
}
