<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Infra\Console\Context\ContextFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\User\Command\ChangeUserCredentialCommand as ChangeUserCredentialDomainCommand;
use MsgPhp\User\Event\UserCredentialChangedEvent;
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
    private $contextFactory;
    private $fields = [];

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $repository, ContextFactoryInterface $contextFactory)
    {
        $this->contextFactory = $contextFactory;

        parent::__construct($factory, $bus, $repository);
    }

    public function onMessageReceived($message): void
    {
        if ($message instanceof UserCredentialChangedEvent) {
            $messages = ['Changed user credential for '.$message->user->getCredential()->getUsername()];
            foreach (array_diff((array) $message->newCredential, $oldValues = (array) $message->oldCredential) as $key => $value) {
                $field = false === ($i = strrpos($key, "\00")) ? $key : substr($key, $i + 1);
                $messages[] = sprintf('> Field "%s" changed from %s to %s', $field, json_encode($oldValues[$key] ?? null), json_encode($value));
            }

            $this->io->success($messages);
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
        $user = $this->getUser($input, $this->io);
        $context = $this->contextFactory->getContext($input, $this->io);

        if (!$context) {
            $field = $this->io->choice('Select a field to change', $this->fields);

            return $this->run(new ArrayInput([
                '--'.$field => null,
                '--by-id' => true,
                'user' => $user->getId()->toString(),
            ]), $output);
        }

        $this->dispatch(ChangeUserCredentialDomainCommand::class, [$user->getId(), $context]);

        return 0;
    }
}
