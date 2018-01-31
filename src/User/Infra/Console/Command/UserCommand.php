<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait, MessageReceivingInterface};
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class UserCommand extends Command implements MessageReceivingInterface
{
    use MessageDispatchingTrait {
        dispatch as protected;
    }

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;

        parent::__construct();
    }

    /**
     * @internal
     */
    public function onMessageReceived($message): void
    {
    }

    protected function configure(): void
    {
        $this
            ->addOption('id', null, InputOption::VALUE_NONE, 'Find user by identifier')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username or identifier value');
    }

    protected function getUser(InputInterface $input, StyleInterface $io): User
    {
        $byId = $input->getOption('id');

        if (null === $username = $input->getArgument('username')) {
            if (!$input->isInteractive()) {
                throw new \LogicException('No value provided for "username".');
            }

            do {
                $username = $io->ask($byId ? 'Identifier' : 'Username');
            } while (null === $username);
        }

        return $byId
            ? $this->repository->find($this->factory->identify(User::class, $username))
            : $this->repository->findByUsername($username);
    }
}
