<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\Domain\CommandBusInterface;
use MsgPhp\User\Command as UserCommand;
use MsgPhp\User\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserRoleCommand extends Command
{
    protected static $defaultName = 'user:role:add';

    private $repository;
    private $commandBus;

    public function __construct(UserRepositoryInterface $repository, CommandBusInterface $commandBus)
    {
        parent::__construct();

        $this->repository = $repository;
        $this->commandBus = $commandBus;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a user role')
            ->addArgument('email', InputArgument::OPTIONAL, 'User e-mail')
            ->addArgument('role', InputArgument::OPTIONAL, 'User role');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->repository->findByEmail($this->getEmail($io, $input));
        $role = $this->getRole($io, $input);

        $this->commandBus->handle(new UserCommand\AddUserRoleCommand($user->getId(), $role));
        $io->success(sprintf('Added role "%s" to user "%s".', $role, $user->getEmail()));

        return 0;
    }

    private function getEmail(StyleInterface $io, InputInterface $input): string
    {
        if ('' === $email = (string) $input->getArgument('email')) {
            if (!$input->isInteractive()) {
                throw new \LogicException('No e-mail provided.');
            }

            do {
                $email = $io->ask('E-mail', null, function ($value) { return $value; });
            } while (null === $email);
        }

        return $email;
    }

    private function getRole(StyleInterface $io, InputInterface $input): string
    {
        if ('' === $role = (string) $input->getArgument('role')) {
            if (!$input->isInteractive()) {
                throw new \LogicException('No role provided.');
            }

            do {
                $role = $io->ask('Role', null, function ($value) { return $value; });
            } while (null === $role);
        }

        return $role;
    }
}
