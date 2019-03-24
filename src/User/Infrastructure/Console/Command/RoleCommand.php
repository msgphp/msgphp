<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\Domain\Message\MessageReceiving;
use MsgPhp\User\Repository\RoleRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class RoleCommand extends Command implements MessageReceiving
{
    use RoleAwareTrait;
    use MessageDispatchingTrait {
        dispatch as protected;
    }

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, RoleRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;

        parent::__construct();
    }

    public function onMessageReceived($message): void
    {
    }

    protected function configure(): void
    {
        $this->addArgument('role', InputArgument::OPTIONAL, 'The role name');
    }
}
