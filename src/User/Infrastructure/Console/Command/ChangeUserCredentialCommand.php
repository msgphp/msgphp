<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Console\Context\ContextFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\User\Command\ChangeUserCredential as ChangeUserCredentialDomainCommand;
use MsgPhp\User\Event\UserCredentialChanged;
use MsgPhp\User\Repository\UserRepository;
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

    /**
     * @var StyleInterface
     */
    private $io;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @var string[]
     */
    private $fields = [];

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $repository, ContextFactory $contextFactory)
    {
        $this->contextFactory = $contextFactory;

        parent::__construct($factory, $bus, $repository);
    }

    public function onMessageReceived($message): void
    {
        if ($message instanceof UserCredentialChanged) {
            $this->io->success('Changed user credential for '.$message->user->getCredential()->getUsername());

            $rows = [];
            $changes = array_diff((array) $message->newCredential, $oldValues = (array) $message->oldCredential);
            foreach ($changes as $key => $value) {
                $field = false === ($i = strrpos($key, "\00")) ? $key : substr($key, $i + 1);
                // @todo use VarDumper
                $rows[] = [$field, json_encode($oldValues[$key] ?? null), json_encode($value)];
            }

            if ($rows) {
                $this->io->table(['Field', 'Old Value', 'New Value'], $rows);
            }
        }
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
        $this->io = new SymfonyStyle($input, $output);
        $userId = $this->getUser($input, $this->io)->getId();
        $fields = $this->contextFactory->getContext($input, $this->io);

        if (!$fields) {
            $field = $this->io->choice('Select a field to change', $this->fields);

            return $this->run(new ArrayInput([
                '--'.$field => null,
                '--by-id' => true,
                'user' => $userId->toString(),
            ]), $output);
        }

        $this->dispatch(ChangeUserCredentialDomainCommand::class, compact('userId', 'fields'));

        return 0;
    }
}
