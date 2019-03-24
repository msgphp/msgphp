<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\GenericDomainCollection;
use MsgPhp\User\UserRole;

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
     * @return DomainCollection|UserRole[]
     */
    public function getRoles(): DomainCollection
    {
        return new GenericDomainCollection($this->roles);
    }
}
