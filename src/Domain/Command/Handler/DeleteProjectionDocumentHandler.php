<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Command\Handler;

use MsgPhp\Domain\Command\DeleteProjectionDocumentCommand;
use MsgPhp\Domain\Event\ProjectionDocumentDeletedEvent;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\Domain\Projection\ProjectionRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteProjectionDocumentHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, ProjectionRepositoryInterface $repository)
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

        $this->repository->delete($document->getType(), $document->getId());
        $this->dispatch(ProjectionDocumentDeletedEvent::class, compact('document'));
    }
}
