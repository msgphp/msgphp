<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\User\Entity\Role;
use MsgPhp\User\Repository\{RoleRepositoryInterface, UserRepositoryInterface};
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class UserRoleCommand extends UserCommand
{
    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $userRepository, RoleRepositoryInterface $roleRepository)
    {
        parent::__construct($factory, $bus, $userRepository);

        $this->repository = $roleRepository;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('role', InputArgument::OPTIONAL, 'The role name');
    }

    protected function getRole(InputInterface $input, StyleInterface $io): Role
    {
        if (null === $value = $input->getArgument('role')) {
            if (!$input->isInteractive()) {
                throw new \LogicException('No value provided for "role".');
            }

            do {
                $value = $io->ask('Role name');
            } while (null === $value);
        }

        return $this->repository->find($value);
    }
}
