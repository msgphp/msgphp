<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Infra\Console\ContextBuilder\ContextBuilderInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command as DomainCommand;
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

    private $contextBuilder;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, ContextBuilderInterface $contextBuilder)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->contextBuilder = $contextBuilder;

        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Create a user');
        $this->contextBuilder->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = $this->contextBuilder->getContext($input, $io);

        $this->dispatch(DomainCommand\CreateUserCommand::class, ['context' => $context]);

        return 0;
    }
}
