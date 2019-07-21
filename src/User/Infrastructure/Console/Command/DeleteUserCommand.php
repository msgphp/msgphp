<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\User\Command\DeleteUser;
use MsgPhp\User\Infrastructure\Console\UserDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserCommand extends UserCommand
{
    protected static $defaultName = 'user:delete';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Delete a user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->getUser($input, $io);
        $userId = $user->getId();

        if ($input->isInteractive() && !$io->confirm('Are you sure you want to delete <comment>'.UserDefinition::getDisplayName($user).'</comment>?')) {
            return 0;
        }

        $this->bus->dispatch($this->factory->create(DeleteUser::class, compact('userId')));
        $io->success('Deleted user '.UserDefinition::getDisplayName($user));

        return 0;
    }
}
