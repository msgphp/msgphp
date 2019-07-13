<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\User\Command\DeleteRole;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteRoleCommand extends RoleCommand
{
    protected static $defaultName = 'role:delete';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Delete a role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $roleName = $this->getRole($input, $io)->getName();

        if ($input->isInteractive() && !$io->confirm('Are you sure you want to delete <comment>'.$roleName.'</comment>?')) {
            return 0;
        }

        $this->dispatch(DeleteRole::class, compact('roleName'));
        $io->success('Deleted role '.$roleName);

        return 0;
    }
}
