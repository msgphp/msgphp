<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Repository\RoleRepository;
use MsgPhp\User\Repository\UserRepository;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class UserRoleCommand extends UserCommand
{
    use RoleAwareTrait;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $userRepository, RoleRepository $roleRepository)
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
