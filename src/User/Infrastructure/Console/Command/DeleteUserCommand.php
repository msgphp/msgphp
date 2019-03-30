<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\User\Command\DeleteUser;
use MsgPhp\User\Event\UserDeleted;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserCommand extends UserCommand
{
    protected static $defaultName = 'user:delete';

    /**
     * @var StyleInterface
     */
    private $io;

    public function onMessageReceived($message): void
    {
        if ($message instanceof UserDeleted) {
            $this->io->success('Deleted user '.self::getUsername($message->user));
        }
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Delete a user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $user = $this->getUser($input, $this->io);
        $userId = $user->getId();

        if ($input->isInteractive() && !$this->io->confirm('Are you sure you want to delete <comment>'.self::getUsername($user).'</comment>?')) {
            return 0;
        }

        $this->dispatch(DeleteUser::class, compact('userId'));

        return 0;
    }
}
