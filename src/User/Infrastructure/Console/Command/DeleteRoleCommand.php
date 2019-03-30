<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\User\Command\DeleteRole;
use MsgPhp\User\Event\RoleDeleted;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteRoleCommand extends RoleCommand
{
    protected static $defaultName = 'role:delete';

    /**
     * @var StyleInterface
     */
    private $io;

    public function onMessageReceived($message): void
    {
        if ($message instanceof RoleDeleted) {
            $this->io->success('Deleted role '.$message->role->getName());
        }
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Delete a role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $roleName = $this->getRole($input, $this->io)->getName();

        if ($input->isInteractive() && !$this->io->confirm('Are you sure you want to delete <comment>'.$roleName.'</comment>?')) {
            return 0;
        }

        $this->dispatch(DeleteRole::class, compact('roleName'));

        return 0;
    }
}
