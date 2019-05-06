<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\User\Repository\UsernameRepository;
use MsgPhp\User\Username;
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

    /**
     * @var UsernameRepository
     */
    private $repository;

    public function __construct(UsernameRepository $repository)
    {
        $this->repository = $repository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Synchronize usernames')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Perform a dry run')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        $usernames = $unknownUsernames = $this->repository->findAll();
        $rows = [];
        $added = $deleted = 0;

        foreach ($this->repository->lookup() as $username) {
            if ($usernames->containsKey($usernameValue = $username->toString())) {
                $unknownUsernames = $unknownUsernames->filter(static function (Username $knownUsername) use ($usernameValue): bool {
                    return $usernameValue !== $knownUsername->toString();
                });

                continue;
            }

            if (!$dryRun) {
                $this->repository->save($username);
            }

            $rows[] = 'Added username <info>'.$usernameValue.'</info> for user <info>'.$username->getUser()->getId()->toString().'</info>';
            ++$added;
        }

        foreach ($unknownUsernames as $unknownUsername) {
            if (!$dryRun) {
                $this->repository->delete($unknownUsername);
            }

            $rows[] = 'Deleted username <info>'.$unknownUsername.'</info> from user <info>'.$unknownUsername->getUser()->getId()->toString().'</info>';
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
