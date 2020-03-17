<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Fixtures\Entities;

use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;
use MsgPhp\Domain\Model\CanBeConfirmed;
use MsgPhp\Domain\Model\CanBeEnabled;
use MsgPhp\User\Credential\EmailPassword;
use MsgPhp\User\Model\EmailPasswordCredential;
use MsgPhp\User\ScalarUserId;
use MsgPhp\User\User;
use MsgPhp\User\UserId;

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestUser extends User implements DomainEventHandler
{
    use DomainEventHandlerTrait;
    use EmailPasswordCredential;
    use CanBeConfirmed;
    use CanBeEnabled;

    /**
     * @var null|int
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\GeneratedValue()
     * @Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    public function __construct(string $email, string $password, ?UserId $id = null)
    {
        $this->credential = new EmailPassword($email, $password);
        $this->id = null === $id || $id->isNil() ? null : (int) $id->toString();
    }

    public function getId(): UserId
    {
        return new ScalarUserId(null === $this->id ? null : (string) $this->id);
    }
}
