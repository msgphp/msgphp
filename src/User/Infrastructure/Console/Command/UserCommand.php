<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\Domain\Message\MessageReceivingInterface;
use MsgPhp\User\Repository\UserRepositoryInterface;
use MsgPhp\User\User;
use MsgPhp\User\UserIdInterface;
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
        __construct as private init;
        dispatch as protected;
    }

    /**
     * @var UserRepositoryInterface
     */
    private $repository;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $repository)
    {
        $this->init($factory, $bus);

        $this->repository = $repository;

        parent::__construct();
    }

    public function onMessageReceived($message): void
    {
    }

    protected function configure(): void
    {
        $this
            ->addOption('by-id', null, InputOption::VALUE_NONE, 'Find user by identifier')
            ->addArgument('user', InputArgument::OPTIONAL, 'The username or user ID')
        ;
    }

    protected function getUser(InputInterface $input, StyleInterface $io): User
    {
        $byId = $input->getOption('by-id');

        if (null === $value = $input->getArgument('user')) {
            if (!$input->isInteractive()) {
                throw new \LogicException('No value provided for "user".');
            }

            do {
                $value = $io->ask($byId ? 'Identifier' : 'Username');
            } while (null === $value);
        }

        return $byId
            ? $this->repository->find($this->factory->create(UserIdInterface::class, ['value' => $value]))
            : $this->repository->findByUsername($value);
    }
}
