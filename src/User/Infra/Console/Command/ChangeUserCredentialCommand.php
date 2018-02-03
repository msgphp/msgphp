<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Infra\Console\ContextBuilder\ContextBuilderInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\User\Command\ChangeUserCredentialCommand as ChangeUserCredentialDomainCommand;
use MsgPhp\User\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserCredentialCommand extends UserCommand
{
    protected static $defaultName = 'user:change-credential';

    /** @var StyleInterface */
    private $io;
    private $contextBuilder;
    private $fields = [];

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $repository, ContextBuilderInterface $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;

        parent::__construct($factory, $bus, $repository);
    }

    public function onMessageReceived($message): void
    {
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Change a user credential');

        $definition = $this->getDefinition();
        $currentFields = array_keys($definition->getOptions() + $definition->getArguments());

        $this->contextBuilder->configure($this->getDefinition());
        $this->fields = array_values(array_diff(array_keys($definition->getOptions() + $definition->getArguments()), $currentFields));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $user = $this->getUser($input, $this->io);
        $context = $this->contextBuilder->getContext($input, $this->io);

        if (!$context) {
            $field = $this->io->choice('Select field to change', $this->fields);

            return $this->run(new ArrayInput([
                '--'.$field => null,
                '--id' => true,
                'username' => $user->getId()->toString(),
            ]), $output);
        }

        $this->dispatch(ChangeUserCredentialDomainCommand::class, [$user->getId(), $context]);

        return 0;
    }
}
