<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\User\Entity\Username;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UsernameRepositoryInterface
{
    /**
     * @return DomainCollectionInterface|Username[]
     */
    public function lookup(): DomainCollectionInterface;

    /**
     * @return DomainCollectionInterface|Username[]
     */
    public function findAll(int $offset = 0, int $limit = 0): DomainCollectionInterface;

    public function find(string $username): Username;

    public function exists(string $username): bool;

    public function save(Username $username): void;

    public function delete(Username $username): void;
}
