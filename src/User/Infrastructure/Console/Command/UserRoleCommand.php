<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\User\Repository\RoleRepositoryInterface;
use MsgPhp\User\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class UserRoleCommand extends UserCommand
{
    use RoleAwareTrait;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $userRepository, RoleRepositoryInterface $roleRepository)
    {
        parent::__construct($factory, $bus, $userRepository);

        $this->repository = $roleRepository;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('role', InputArgument::OPTIONAL, 'The role name');
    }
}
