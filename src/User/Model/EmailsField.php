<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\GenericDomainCollection;
use MsgPhp\User\Entity\UserEmail;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EmailsField
{
    /**
     * @var iterable|UserEmail[]
     */
    private $emails = [];

    /**
     * @return DomainCollectionInterface|UserEmail[]
     */
    public function getEmails(): DomainCollectionInterface
    {
        return new GenericDomainCollection($this->emails);
    }
}
