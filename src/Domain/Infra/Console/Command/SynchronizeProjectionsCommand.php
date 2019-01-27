<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\Command;

use MsgPhp\Domain\Projection\{ProjectionDocument, ProjectionSynchronization};
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

    private $synchronization;
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
        $succeed = $failed = 0;

        foreach ($this->synchronization->synchronize() as $document) {
            if (ProjectionDocument::STATUS_SYNCHRONIZED === $document->status) {
                ++$succeed;
            } else {
                ++$failed;
            }

            if (null !== $document->error && null !== $this->logger) {
                $this->logger->error($document->error->getMessage(), ['exception' => $document->error]);
            }
        }

        $io->success($succeed.' projection '.(1 === $succeed ? 'document' : 'documents').' synchronized');

        if ($failed) {
            $io->error($failed.' projection '.(1 === $failed ? 'document' : 'documents').' failed');
        }

        return 0;
    }
}
