<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\Domain\Projection\Command\DeleteProjectionDocumentCommand;
use MsgPhp\Domain\Projection\Event\ProjectionDocumentDeletedEvent;
use MsgPhp\Domain\Projection\ProjectionRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteProjectionDocumentHandler
{
    use MessageDispatchingTrait;

    /**
     * @var ProjectionRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, ProjectionRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(DeleteProjectionDocumentCommand $command): void
    {
        if (null === $document = $this->repository->find($command->type, $command->id)) {
            return;
        }

        if (null === $type = $document->getType()) {
            throw new \LogicException('Document must have a type.');
        }

        if (null === $id = $document->getId()) {
            throw new \LogicException('Document must have an ID.');
        }

        $this->repository->delete($type, $id);
        $this->dispatch(ProjectionDocumentDeletedEvent::class, compact('document'));
    }
}
