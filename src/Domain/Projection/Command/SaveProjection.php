<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class SaveProjection
{
    public $type;
    public $document;

    public function __construct(string $type, array $document)
    {
        $this->type = $type;
        $this->document = $document;
    }
}
