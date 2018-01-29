<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\Domain\Message\{DomainMessageBusInterface, DomainMessageDispatchingTrait};
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class UserCommand extends Command
{
    use DomainMessageDispatchingTrait {
        dispatch as protected;
    }

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $repository)
    {
        parent::__construct();

        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    protected function configure(): void
    {
        $this
            ->addOption('id', null, InputOption::VALUE_NONE, 'Find user by identifier')
            ->addArgument('username', null, 'The username or identifier value');
    }

    protected function getUser(InputInterface $input, StyleInterface $io): User
    {
        $byId = $input->getOption('id');
        $label = $byId ? 'identifier' : 'username';

        if (null === $username = $input->getArgument('username')) {
            if (!$input->isInteractive()) {
                throw new \LogicException(sprintf('No %s provided.', $label));
            }

            do {
                $username = $io->ask(ucfirst($label));
            } while (null === $username);
        }

        return $byId
            ? $this->repository->find($this->factory->identify(User::class, $username))
            : $this->repository->findByUsername($username);
    }
}
