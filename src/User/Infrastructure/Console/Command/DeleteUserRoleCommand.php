<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Command\DeleteUserRole;
use MsgPhp\User\Infrastructure\Console\Definition\RoleDefinition;
use MsgPhp\User\Infrastructure\Console\Definition\UserDefinition;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserRoleCommand extends Command
{
    protected static $defaultName = 'user:role:delete';

    /** @var DomainObjectFactory */
    private $factory;

    /** @var DomainMessageBus */
    private $bus;

    /** @var UserDefinition */
    private $userDefinition;

    /** @var RoleDefinition */
    private $roleDefinition;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserDefinition $userDefinition, RoleDefinition $roleDefinition)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->userDefinition = $userDefinition;
        $this->roleDefinition = $roleDefinition;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Delete a user role');
        $this->userDefinition->configure($this->getDefinition());
        $this->roleDefinition->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->userDefinition->getUser($input, $io);
        $userId = $user->getId();
        $roleName = $this->roleDefinition->getRole($input, $io)->getName();

        $this->bus->dispatch($this->factory->create(DeleteUserRole::class, compact('userId', 'roleName')));
        $io->success('Deleted role '.$roleName.' from user '.UserDefinition::getDisplayName($user));

        return 0;
    }
}
