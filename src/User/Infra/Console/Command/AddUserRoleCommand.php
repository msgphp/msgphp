<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Infra\Console\ContextBuilder\ContextBuilderInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\User\Command\AddUserRoleCommand as AddUserRoleDomainCommand;
use MsgPhp\User\Event\UserRoleAddedEvent;
use MsgPhp\User\Repository\{RoleRepositoryInterface, UserRepositoryInterface};
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
    private $contextBuilder;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $userRepository, RoleRepositoryInterface $roleRepository, ContextBuilderInterface $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;

        parent::__construct($factory, $bus, $userRepository, $roleRepository);
    }

    public function onMessageReceived($message): void
    {
        if ($message instanceof UserRoleAddedEvent) {
            $this->io->success(sprintf('Added role "%s" to user %s', $message->userRole->getRoleName(), $message->userRole->getUser()->getCredential()->getUsername()));
        }
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Add a user role');
        $this->contextBuilder->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $user = $this->getUser($input, $this->io);
        $role = $this->getRole($input, $this->io);
        $context = $this->contextBuilder->getContext($input, $this->io, ['user' => $user, 'role' => $role]);

        $this->dispatch(AddUserRoleDomainCommand::class, [$user->getId(), $role->getName(), $context]);

        return 0;
    }
}
