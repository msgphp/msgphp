<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\User\Command\EnableUser;
use MsgPhp\User\Event\UserEnabled;
use MsgPhp\User\Infrastructure\Console\UserDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EnableUserCommand extends UserCommand
{
    protected static $defaultName = 'user:enable';

    /** @var StyleInterface */
    private $io;

    public function onMessageReceived(object $message): void
    {
        if ($message instanceof UserEnabled) {
        }
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Enable a user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->getUser($input, $io);
        $userId = $user->getId();

        $this->dispatch(EnableUser::class, compact('userId'));
        $io->success('Enabled user '.UserDefinition::getDisplayName($user));

        return 0;
    }
}
