<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Exception\EntityNotFound;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Console\Definition\DomainContextDefinition;
use MsgPhp\User\Command\AddUserRole;
use MsgPhp\User\Infrastructure\Console\Definition\RoleDefinition;
use MsgPhp\User\Infrastructure\Console\Definition\UserDefinition;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserRoleCommand extends Command
{
    protected static $defaultName = 'user:role:add';

    /** @var DomainObjectFactory */
    private $factory;

    /** @var DomainMessageBus */
    private $bus;

    /** @var UserDefinition */
    private $userDefinition;

    /** @var RoleDefinition */
    private $roleDefinition;

    /** @var DomainContextDefinition */
    private $contextDefinition;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserDefinition $userDefinition, RoleDefinition $roleDefinition, DomainContextDefinition $contextDefinition)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->userDefinition = $userDefinition;
        $this->roleDefinition = $roleDefinition;
        $this->contextDefinition = $contextDefinition;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Add a user role');
        $this->userDefinition->configure($this->getDefinition());
        $this->roleDefinition->configure($this->getDefinition());
        $this->contextDefinition->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->userDefinition->getUser($input, $io);

        try {
            $role = $this->roleDefinition->getRole($input, $io);
        } catch (EntityNotFound $e) {
            $roleName = $input->getArgument('role');

            if (!\is_string($roleName)) {
                throw new \UnexpectedValueException('Role name must be a string.');
            }

            if (!$input->isInteractive() || !$io->confirm('Role <comment>'.$roleName.'</comment> does not exists. Create it now?')) {
                throw $e;
            }

            $this->createRole($roleName, $output);

            $role = $this->roleDefinition->getRole($input, $io);
        }

        $userId = $user->getId();
        $roleName = $role->getName();
        $context = $this->contextDefinition->getContext($input, $io, compact('user', 'role'));

        $this->bus->dispatch($this->factory->create(AddUserRole::class, compact('userId', 'roleName', 'context')));
        $io->success('Added role '.$roleName.' to user '.UserDefinition::getDisplayName($user));

        return 0;
    }

    private function createRole(string $name, OutputInterface $output): void
    {
        if (null === $application = $this->getApplication()) {
            throw new \LogicException('Application not set.');
        }

        if (0 !== $application->find($command = 'role:create')->run(new ArrayInput([
            'command' => $command,
            'name' => $name,
        ]), $output)) {
            throw new \RuntimeException('Cannot create role "'.$name.'". Something went wrong.');
        }
    }
}
