<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\Domain\Message\MessageReceiving;
use MsgPhp\User\Credential\UsernameCredential;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\User;
use MsgPhp\User\UserId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class UserCommand extends Command implements MessageReceiving
{
    use MessageDispatchingTrait {
        __construct as private init;
        dispatch as protected;
    }

    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $repository)
    {
        $this->init($factory, $bus);

        $this->repository = $repository;

        parent::__construct();
    }

    public function onMessageReceived($message): void
    {
    }

    final public static function getUsername(User $user): string
    {
        $credential = $user->getCredential();

        return $credential instanceof UsernameCredential ? $credential->getUsername() : $user->getId()->toString();
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
            ? $this->repository->find($this->factory->create(UserId::class, ['value' => $value]))
            : $this->repository->findByUsername($value);
    }
}
