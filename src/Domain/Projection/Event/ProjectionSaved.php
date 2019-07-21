<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection\Event;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ProjectionSaved
{
    public $type;
    public $document;

    public function __construct(string $type, array $document)
    {
        $this->type = $type;
        $this->document = $document;
    }
}
