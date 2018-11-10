<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\User\Command\DeleteUserRoleCommand as DeleteUserRoleDomainCommand;
use MsgPhp\User\Event\UserRoleDeletedEvent;
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

    /** @var StyleInterface */
    private $io;

    public function onMessageReceived($message): void
    {
        if ($message instanceof UserRoleDeletedEvent) {
            $this->io->success(sprintf('Deleted role %s from user %s', $message->userRole->getRoleName(), $message->userRole->getUser()->getCredential()->getUsername()));
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
        $user = $this->getUser($input, $this->io);
        $role = $this->getRole($input, $this->io);

        $this->dispatch(DeleteUserRoleDomainCommand::class, [$user->getId(), $role->getName()]);

        return 0;
    }
}
