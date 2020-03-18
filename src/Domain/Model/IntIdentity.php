<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Model;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\GenericDomainId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait IntIdentity
{
    /** @var null|int */
    private $id;

    public function getId(): DomainId
    {
        return GenericDomainId::fromInt($this->id);
    }
}
