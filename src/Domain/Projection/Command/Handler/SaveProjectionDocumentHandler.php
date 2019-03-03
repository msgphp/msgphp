<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection\Command\Handler;

use MsgPhp\Domain\Event\ProjectionDocumentSavedEvent;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\Domain\Projection\Command\SaveProjectionDocumentCommand;
use MsgPhp\Domain\Projection\ProjectionDocument;
use MsgPhp\Domain\Projection\ProjectionRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SaveProjectionDocumentHandler
{
    use MessageDispatchingTrait;

    /**
     * @var ProjectionRepositoryInterface
     */
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
