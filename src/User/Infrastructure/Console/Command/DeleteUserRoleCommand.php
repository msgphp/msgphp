<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\User\Command\DeleteUserRole;
use MsgPhp\User\Event\UserRoleDeleted;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserRoleCommand extends UserRoleCommand
{
    protected static $defaultName = 'user:role:delete';

    /**
     * @var StyleInterface
     */
    private $io;

    public function onMessageReceived($message): void
    {
        if ($message instanceof UserRoleDeleted) {
            $this->io->success('Deleted role '.$message->userRole->getRoleName().' from user '.self::getUsername($message->userRole->getUser()));
        }
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Delete a user role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $userId = $this->getUser($input, $this->io)->getId();
        $roleName = $this->getRole($input, $this->io)->getName();

        $this->dispatch(DeleteUserRole::class, compact('userId', 'roleName'));

        return 0;
    }
}
