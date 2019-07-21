<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Console\Command;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Command\DeleteRole;
use MsgPhp\User\Repository\RoleRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteRoleCommand extends Command
{
    use RoleAwareTrait;

    protected static $defaultName = 'role:delete';

    /** @var DomainObjectFactory */
    private $factory;
    /** @var DomainMessageBus */
    private $bus;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, RoleRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;

        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription('Delete a role')
            ->addArgument('role', InputArgument::OPTIONAL, 'The role name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $roleName = $this->getRole($input, $io)->getName();

        if ($input->isInteractive() && !$io->confirm('Are you sure you want to delete <comment>'.$roleName.'</comment>?')) {
            return 0;
        }

        $this->bus->dispatch($this->factory->create(DeleteRole::class, compact('roleName')));
        $io->success('Deleted role '.$roleName);

        return 0;
    }
}
