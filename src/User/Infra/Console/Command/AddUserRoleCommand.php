<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Infra\Console\Context\ContextFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\User\Command\AddUserRoleCommand as AddUserRoleDomainCommand;
use MsgPhp\User\Event\{RoleCreatedEvent, UserRoleAddedEvent};
use MsgPhp\User\Repository\{RoleRepositoryInterface, UserRepositoryInterface};
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserRoleCommand extends UserRoleCommand
{
    protected static $defaultName = 'user:role:add';

    /** @var StyleInterface */
    private $io;
    private $contextFactory;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $userRepository, RoleRepositoryInterface $roleRepository, ContextFactoryInterface $contextFactory)
    {
        $this->contextFactory = $contextFactory;

        parent::__construct($factory, $bus, $userRepository, $roleRepository);
    }

    public function onMessageReceived($message): void
    {
        if ($message instanceof RoleCreatedEvent) {
            $this->io->success('Created role '.$message->role->getName());
        }
        if ($message instanceof UserRoleAddedEvent) {
            $this->io->success(sprintf('Added role %s to user %s', $message->userRole->getRoleName(), $message->userRole->getUser()->getCredential()->getUsername()));
        }
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Add a user role');
        $this->contextFactory->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $user = $this->getUser($input, $this->io);

        try {
            $role = $this->getRole($input, $this->io);
        } catch (EntityNotFoundException $e) {
            $roleName = $input->getArgument('role');

            if (!$input->isInteractive() || !$this->io->confirm(sprintf('Role "%s" does not exists. Create it now?', $roleName))) {
                throw $e;
            }

            $command = $this->getApplication()->find($commandName = 'role:create');
            $result = $command->run(new ArrayInput([
                'command' => $commandName,
                'name' => $roleName,
            ]), $this->io);

            if (0 !== $result) {
                throw new \RuntimeException(sprintf('Cannot create role "%s". Something went wrong.', $roleName));
            }

            $role = $this->getRole($input, $this->io);
        }

        $userId = $user->getId();
        $roleName = $role->getName();
        $context = $this->contextFactory->getContext($input, $this->io, compact('user', 'role'));

        $this->dispatch(AddUserRoleDomainCommand::class, compact('userId', 'roleName', 'context'));

        return 0;
    }
}
