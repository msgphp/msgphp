<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\User\Command as DomainCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EnableUserCommand extends UserCommand
{
    protected static $defaultName = 'user:enable';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Enable a user');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->getUser($input, $io);

        $this->dispatch(new DomainCommand\EnableUserCommand($user->getId()));

        $io->success('Enabled user '.$user->getCredential()->getUsername());

        return 0;
    }
}
