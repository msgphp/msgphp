<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\GenericDomainCollection;
use MsgPhp\User\Entity\UserRole;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait RolesField
{
    /**
     * @var iterable|UserRole[]
     */
    private $roles = [];

    /**
     * @return DomainCollectionInterface|UserRole[]
     */
    public function getRoles(): DomainCollectionInterface
    {
        return new GenericDomainCollection($this->roles);
    }
}
