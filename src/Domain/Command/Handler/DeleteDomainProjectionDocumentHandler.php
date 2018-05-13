<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Command\Handler;

use MsgPhp\Domain\Command\DeleteDomainProjectionDocumentCommand;
use MsgPhp\Domain\Event\DomainProjectionDocumentDeletedEvent;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\Domain\Projection\DomainProjectionRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteDomainProjectionDocumentHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, DomainProjectionRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(DeleteDomainProjectionDocumentCommand $command): void
    {
        if (null === $document = $this->repository->find($command->type, $command->id)) {
            return;
        }

        $this->repository->delete($command->type, $command->id);
        $this->dispatch(DomainProjectionDocumentDeletedEvent::class, [$document]);
    }
}
