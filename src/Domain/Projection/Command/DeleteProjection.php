<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DeleteProjection
{
    public $type;
    public $id;

    public function __construct(string $type, string $id)
    {
        $this->type = $type;
        $this->id = $id;
    }
}
