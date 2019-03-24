<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\User\Command\EnableUser as EnableUserDomainCommand;
use MsgPhp\User\Event\UserEnabled;
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

    /**
     * @var StyleInterface
     */
    private $io;

    public function onMessageReceived($message): void
    {
        if ($message instanceof UserEnabled) {
            $this->io->success('Enabled user '.$message->user->getCredential()->getUsername());
        }
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Enable a user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $userId = $this->getUser($input, $this->io)->getId();

        $this->dispatch(EnableUserDomainCommand::class, compact('userId'));

        return 0;
    }
}
