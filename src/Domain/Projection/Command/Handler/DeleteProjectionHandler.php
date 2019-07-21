<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection\Command\Handler;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Projection\Command\DeleteProjection;
use MsgPhp\Domain\Projection\Event\ProjectionDeleted;
use MsgPhp\Domain\Projection\ProjectionRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteProjectionHandler
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

    public function __invoke(DeleteProjection $command): void
    {
        if ($this->repository->delete($type = $command->type, $id = $command->id)) {
            $this->bus->dispatch($this->factory->create(ProjectionDeleted::class, compact('type', 'id')));
        }
    }
}
