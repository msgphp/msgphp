<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\Domain\Projection\Command\SaveProjection;
use MsgPhp\Domain\Projection\Event\ProjectionSaved;
use MsgPhp\Domain\Projection\ProjectionRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SaveProjectionHandler
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

    public function __invoke(SaveProjection $command): void
    {
        $this->repository->save($type = $command->type, $document = $command->document);
        $this->dispatch(ProjectionSaved::class, compact('type', 'document'));
    }
}
