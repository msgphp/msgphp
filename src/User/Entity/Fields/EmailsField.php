<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Fields;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Factory\DomainCollectionFactory;
use MsgPhp\User\Entity\UserEmail;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EmailsField
{
    /** @var UserEmail[] */
    private $emails = [];

    public function getEmails(): DomainCollectionInterface
    {
        return $this->emails instanceof DomainCollectionInterface ? $this->emails : DomainCollectionFactory::create($this->emails);
    }
}
