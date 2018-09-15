<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\User\Entity\Username;
use MsgPhp\User\Repository\UsernameRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SynchronizeUsernamesCommand extends Command
{
    protected static $defaultName = 'user:synchronize-usernames';

    private $repository;

    public function __construct(UsernameRepositoryInterface $repository)
    {
        $this->repository = $repository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Synchronize usernames')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Perform a dry run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        $usernames = $unknownUsernames = $this->repository->findAll();
        $rows = [];
        $added = $deleted = 0;

        foreach ($this->repository->findAllFromTargets() as $username) {
            if ($usernames->containsKey($usernameValue = (string) $username)) {
                $unknownUsernames = $unknownUsernames->filter(function (Username $knownUsername) use ($usernameValue): bool {
                    return $usernameValue !== (string) $knownUsername;
                });

                continue;
            }

            if (!$dryRun) {
                $this->repository->save($username);
            }

            $rows[] = sprintf('Added username <info>%s</info> for user <info>%s</info>', $usernameValue, $username->getUser()->getId()->toString());
            ++$added;
        }

        foreach ($unknownUsernames as $unknownUsername) {
            if (!$dryRun) {
                $this->repository->delete($unknownUsername);
            }

            $rows[] = sprintf('Deleted username <info>%s</info> from user <info>%s</info>', (string) $unknownUsername, $unknownUsername->getUser()->getId()->toString());
            ++$deleted;
        }

        if ($rows) {
            $io->listing($rows);
        }

        if ($added || $deleted) {
            $io->success([
                $added.' '.(1 === $added ? 'username' : 'usernames').' added',
                $deleted.' '.(1 === $deleted ? 'username' : 'usernames').' deleted',
            ]);
        } else {
            $io->success('All usernames are in sync');
        }

        if ($dryRun) {
            $io->warning('This was a dry run, nothing has changed!');
        }

        return 0;
    }
}
