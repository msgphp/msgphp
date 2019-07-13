<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Console\Context\ContextFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
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

    /** @var ContextFactory */
    private $contextFactory;
    /** @var array<int, string> */
    private $fields = [];

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $repository, ContextFactory $contextFactory)
    {
        $this->contextFactory = $contextFactory;

        parent::__construct($factory, $bus, $repository);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Change a user credential');

        $definition = $this->getDefinition();
        $currentFields = array_keys($definition->getOptions() + $definition->getArguments());

        $this->contextFactory->configure($this->getDefinition());
        $this->fields = array_values(array_diff(array_keys($definition->getOptions() + $definition->getArguments()), $currentFields));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->getUser($input, $io);
        $userId = $user->getId();
        $fields = $this->contextFactory->getContext($input, $io);

        if (!$fields) {
            $field = $io->choice('Select a field to change', $this->fields);

            return $this->run(new ArrayInput([
                '--'.$field => null,
                '--by-id' => true,
                'user' => $userId->toString(),
            ]), $output);
        }

        $this->dispatch(ChangeUserCredential::class, compact('userId', 'fields'));
        $io->success('Changed user credential for '.UserDefinition::getDisplayName($user));

        return 0;
    }
}
