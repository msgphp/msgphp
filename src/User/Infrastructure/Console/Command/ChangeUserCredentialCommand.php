<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Console\Definition\DomainContextDefinition;
use MsgPhp\User\Command\ChangeUserCredential;
use MsgPhp\User\Infrastructure\Console\Definition\UserDefinition;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserCredentialCommand extends Command
{
    protected static $defaultName = 'user:change-credential';

    /** @var DomainObjectFactory */
    private $factory;

    /** @var DomainMessageBus */
    private $bus;

    /** @var UserDefinition */
    private $definition;

    /** @var DomainContextDefinition */
    private $contextDefinition;

    /** @var array<int, string> */
    private $fields = [];

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserDefinition $definition, DomainContextDefinition $contextDefinition)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->definition = $definition;
        $this->contextDefinition = $contextDefinition;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Change a user credential');
        $this->definition->configure($this->getDefinition());

        $currentFields = array_keys($this->getDefinition()->getOptions() + $this->getDefinition()->getArguments());
        $this->contextDefinition->configure($this->getDefinition());
        $this->fields = array_values(array_diff(array_keys($this->getDefinition()->getOptions() + $this->getDefinition()->getArguments()), $currentFields));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->definition->getUser($input, $io);
        $userId = $user->getId();
        $fields = $this->contextDefinition->getContext($input, $io);

        if (!$fields) {
            $field = $io->choice('Select a field to change', $this->fields);

            return $this->run(new ArrayInput([
                '--'.$field => null,
                '--by-id' => true,
                'user' => $userId->toString(),
            ]), $output);
        }

        $this->bus->dispatch($this->factory->create(ChangeUserCredential::class, compact('userId', 'fields')));
        $io->success('Changed user credential for '.UserDefinition::getDisplayName($user));

        return 0;
    }
}
