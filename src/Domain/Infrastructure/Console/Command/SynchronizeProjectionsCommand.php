<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Console\Command;

use MsgPhp\Domain\Projection\ProjectionSynchronization;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SynchronizeProjectionsCommand extends Command
{
    protected static $defaultName = 'projection:synchronize';

    /** @var ProjectionSynchronization */
    private $synchronization;
    /** @var LoggerInterface|null */
    private $logger;

    public function __construct(ProjectionSynchronization $synchronization, LoggerInterface $logger = null)
    {
        $this->synchronization = $synchronization;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Synchronizes all projections')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $synchronized = $this->synchronization->synchronize();

        $io->success($synchronized.' projections are synchronized');

        return 0;
    }
}
