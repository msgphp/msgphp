<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Command\ConfirmUser;
use MsgPhp\User\Infrastructure\Console\Definition\UserDefinition;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConfirmUserCommand extends Command
{
    protected static $defaultName = 'user:confirm';

    /** @var DomainObjectFactory */
    private $factory;

    /** @var DomainMessageBus */
    private $bus;

    /** @var UserDefinition */
    private $definition;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserDefinition $definition)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->definition = $definition;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Confirm a user');
        $this->definition->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->definition->getUser($input, $io);
        $userId = $user->getId();

        $this->bus->dispatch($this->factory->create(ConfirmUser::class, compact('userId')));
        $io->success('Confirmed user '.UserDefinition::getDisplayName($user));

        return 0;
    }
}
