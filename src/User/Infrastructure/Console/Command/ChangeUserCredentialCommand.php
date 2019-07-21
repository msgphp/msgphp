<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Console\Definition\DomainContextDefinition;
use MsgPhp\User\Command\ChangeUserCredential;
use MsgPhp\User\Infrastructure\Console\UserDefinition;
use MsgPhp\User\Repository\UserRepository;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserCredentialCommand extends UserCommand
{
    protected static $defaultName = 'user:change-credential';

    /** @var DomainContextDefinition */
    private $definition;
    /** @var array<int, string> */
    private $fields = [];

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $repository, DomainContextDefinition $definition)
    {
        $this->definition = $definition;

        parent::__construct($factory, $bus, $repository);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Change a user credential');

        $definition = $this->getDefinition();
        $currentFields = array_keys($definition->getOptions() + $definition->getArguments());

        $this->definition->configure($this->getDefinition());
        $this->fields = array_values(array_diff(array_keys($definition->getOptions() + $definition->getArguments()), $currentFields));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->getUser($input, $io);
        $userId = $user->getId();
        $fields = $this->definition->getContext($input, $io);

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
