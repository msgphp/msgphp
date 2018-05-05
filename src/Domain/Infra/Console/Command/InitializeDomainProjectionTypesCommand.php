<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\Command;

use MsgPhp\Domain\Projection\DomainProjectionTypeRegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class InitializeDomainProjectionTypesCommand extends Command
{
    protected static $defaultName = 'projection:initialize-types';

    private $typeRegistry;

    public function __construct(DomainProjectionTypeRegistryInterface $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Initializes all projection types')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force initialization by destroying types first');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('force')) {
            $this->typeRegistry->destroy();
        }

        $this->typeRegistry->initialize();

        $io->success('Projection types successfully initialized');
        $io->listing($this->typeRegistry->all());

        return 0;
    }
}
