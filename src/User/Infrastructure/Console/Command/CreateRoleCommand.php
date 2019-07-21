<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Console\Definition\DomainContextDefinition;
use MsgPhp\User\Command\CreateRole;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateRoleCommand extends Command
{
    protected static $defaultName = 'role:create';

    /** @var DomainObjectFactory */
    private $factory;
    /** @var DomainMessageBus */
    private $bus;
    /** @var DomainContextDefinition */
    private $definition;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, DomainContextDefinition $definition)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->definition = $definition;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Create a role');
        $this->definition->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = $this->definition->getContext($input, $io);

        $this->bus->dispatch($this->factory->create(CreateRole::class, compact('context')));
        $io->success('Role created');

        return 0;
    }
}
