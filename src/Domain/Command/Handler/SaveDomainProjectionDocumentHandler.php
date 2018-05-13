<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Command\Handler;

use MsgPhp\Domain\Command\SaveDomainProjectionDocumentCommand;
use MsgPhp\Domain\Event\DomainProjectionDocumentSavedEvent;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\Domain\Projection\{DomainProjectionDocument, DomainProjectionRepositoryInterface};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SaveDomainProjectionDocumentHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, DomainProjectionRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(SaveDomainProjectionDocumentCommand $command): void
    {
        $document = new DomainProjectionDocument($command->type, $command->id, $command->body);

        $this->repository->save($document);
        $this->dispatch(DomainProjectionDocumentSavedEvent::class, [$document]);
    }
}
