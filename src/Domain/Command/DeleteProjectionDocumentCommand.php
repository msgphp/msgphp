<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DeleteProjectionDocumentCommand
{
    public $type;
    public $id;

    final public function __construct(string $type, string $id)
    {
        $this->type = $type;
        $this->id = $id;
    }
}
