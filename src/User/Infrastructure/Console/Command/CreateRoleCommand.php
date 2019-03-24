<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Console\Context\ContextFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\Domain\Message\MessageReceiving;
use MsgPhp\User\Command\CreateRole as CreateRoleDomainCommand;
use MsgPhp\User\Event\RoleCreated;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateRoleCommand extends Command implements MessageReceiving
{
    use MessageDispatchingTrait;

    protected static $defaultName = 'role:create';

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @var StyleInterface
     */
    private $io;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, ContextFactory $contextFactory)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->contextFactory = $contextFactory;

        parent::__construct();
    }

    public function onMessageReceived($message): void
    {
        if ($message instanceof RoleCreated) {
            $this->io->success('Created role '.$message->role->getName());
        }
    }

    protected function configure(): void
    {
        $this->setDescription('Create a role');
        $this->contextFactory->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $context = $this->contextFactory->getContext($input, $this->io);

        $this->dispatch(CreateRoleDomainCommand::class, compact('context'));

        return 0;
    }
}
