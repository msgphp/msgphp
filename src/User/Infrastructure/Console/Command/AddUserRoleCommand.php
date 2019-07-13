<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Console\Context\ContextFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\User\Command\AddUserRole;
use MsgPhp\User\Infrastructure\Console\UserDefinition;
use MsgPhp\User\Repository\RoleRepository;
use MsgPhp\User\Repository\UserRepository;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserRoleCommand extends UserRoleCommand
{
    protected static $defaultName = 'user:role:add';

    /** @var ContextFactory */
    private $contextFactory;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $userRepository, RoleRepository $roleRepository, ContextFactory $contextFactory)
    {
        $this->contextFactory = $contextFactory;

        parent::__construct($factory, $bus, $userRepository, $roleRepository);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Add a user role');
        $this->contextFactory->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->getUser($input, $io);

        try {
            $role = $this->getRole($input, $io);
        } catch (EntityNotFoundException $e) {
            $roleName = $input->getArgument('role');

            if (!\is_string($roleName)) {
                throw new \UnexpectedValueException('Role name must be a string.');
            }

            if (!$input->isInteractive() || !$io->confirm('Role <comment>'.$roleName.'</comment> does not exists. Create it now?')) {
                throw $e;
            }

            $command = $this->getApplication()->find($commandName = 'role:create');
            $result = $command->run(new ArrayInput([
                'command' => $commandName,
                'name' => $roleName,
            ]), $io);

            if (0 !== $result) {
                throw new \RuntimeException('Cannot create role "'.$roleName.'". Something went wrong.');
            }

            $role = $this->getRole($input, $io);
        }

        $userId = $user->getId();
        $roleName = $role->getName();
        $context = $this->contextFactory->getContext($input, $io, compact('user', 'role'));

        $this->dispatch(AddUserRole::class, compact('userId', 'roleName', 'context'));
        $io->success('Added role '.$roleName.' to user '.UserDefinition::getDisplayName($user));

        return 0;
    }
}
