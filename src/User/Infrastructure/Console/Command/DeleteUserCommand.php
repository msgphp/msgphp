<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Command\DeleteUser;
use MsgPhp\User\Infrastructure\Console\Definition\UserDefinition;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserCommand extends Command
{
    protected static $defaultName = 'user:delete';

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
        $this->setDescription('Delete a user');
        $this->definition->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->definition->getUser($input, $io);
        $userId = $user->getId();

        if (!$io->confirm('Are you sure you want to delete <comment>'.UserDefinition::getDisplayName($user).'</comment>?')) {
            return 0;
        }

        $this->bus->dispatch($this->factory->create(DeleteUser::class, compact('userId')));
        $io->success('Deleted user '.UserDefinition::getDisplayName($user));

        return 0;
    }
}
