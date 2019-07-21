<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection\Command\Handler;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Projection\Command\SaveProjection;
use MsgPhp\Domain\Projection\Event\ProjectionSaved;
use MsgPhp\Domain\Projection\ProjectionRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SaveProjectionHandler
{
    private $factory;
    private $bus;
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, ProjectionRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(SaveProjection $command): void
    {
        $this->repository->save($type = $command->type, $document = $command->document);
        $this->bus->dispatch($this->factory->create(ProjectionSaved::class, compact('type', 'document')));
    }
}
