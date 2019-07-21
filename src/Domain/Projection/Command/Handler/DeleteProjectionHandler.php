<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\Domain\Projection\Command\DeleteProjection;
use MsgPhp\Domain\Projection\Event\ProjectionDeleted;
use MsgPhp\Domain\Projection\ProjectionRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteProjectionHandler
{
    use MessageDispatchingTrait;

    /** @var ProjectionRepository */
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
            $this->dispatch(ProjectionDeleted::class, compact('type', 'id'));
        }
    }
}
