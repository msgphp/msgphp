<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection\Event;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ProjectionDeleted
{
    public $type;
    public $id;

    public function __construct(string $type, string $id)
    {
        $this->type = $type;
        $this->id = $id;
    }
}
