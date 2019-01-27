<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Fields;

use MsgPhp\Domain\{DomainCollection, DomainCollectionInterface};
use MsgPhp\User\Entity\UserEmail;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EmailsField
{
    /** @var iterable|UserEmail[] */
    private $emails = [];

    /**
     * @return DomainCollectionInterface|UserEmail[]
     */
    public function getEmails(): DomainCollectionInterface
    {
        return new DomainCollection($this->emails);
    }
}
