<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait, MessageReceivingInterface};
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\User\Repository\RoleRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class RoleCommand extends Command implements MessageReceivingInterface
{
    use RoleAwareTrait;
    use MessageDispatchingTrait {
        dispatch as protected;
    }

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, RoleRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;

        parent::__construct();
    }

    /**
     * @internal
     */
    public function onMessageReceived($message): void
    {
    }

    protected function configure(): void
    {
        $this->addArgument('role', InputArgument::OPTIONAL, 'The role name');
    }
}
