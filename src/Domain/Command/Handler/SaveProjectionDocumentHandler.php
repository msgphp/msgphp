<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Command\Handler;

use MsgPhp\Domain\Command\SaveProjectionDocumentCommand;
use MsgPhp\Domain\Event\ProjectionDocumentSavedEvent;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\Domain\Projection\{ProjectionDocument, ProjectionRepositoryInterface};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SaveProjectionDocumentHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, ProjectionRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(SaveProjectionDocumentCommand $command): void
    {
        $document = new ProjectionDocument($command->type, $command->id, $command->body);

        $this->repository->save($document);
        $this->dispatch(ProjectionDocumentSavedEvent::class, compact('document'));
    }
}
