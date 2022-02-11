<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Fixtures\Entities;

use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;
use MsgPhp\Domain\Model\CanBeConfirmed;
use MsgPhp\User\UserEmail;

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestUserEmail extends UserEmail implements DomainEventHandler
{
    use CanBeConfirmed;
    use DomainEventHandlerTrait;
}
