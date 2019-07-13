<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Console\Context\ContextFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\CreateUser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateUserCommand extends Command
{
    use MessageDispatchingTrait;

    protected static $defaultName = 'user:create';

    /** @var ContextFactory */
    private $contextFactory;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, ContextFactory $contextFactory)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->contextFactory = $contextFactory;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Create a user');
        $this->contextFactory->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = $this->contextFactory->getContext($input, $io);

        $this->dispatch(CreateUser::class, compact('context'));
        $io->success('User created');

        return 0;
    }
}
