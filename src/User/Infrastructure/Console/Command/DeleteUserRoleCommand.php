<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\User\Command\DeleteUserRole;
use MsgPhp\User\Infrastructure\Console\UserDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserRoleCommand extends UserRoleCommand
{
    protected static $defaultName = 'user:role:delete';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Delete a user role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->getUser($input, $io);
        $userId = $user->getId();
        $roleName = $this->getRole($input, $io)->getName();

        $this->bus->dispatch($this->factory->create(DeleteUserRole::class, compact('userId', 'roleName')));
        $io->success('Deleted role '.$roleName.' from user '.UserDefinition::getDisplayName($user));

        return 0;
    }
}
