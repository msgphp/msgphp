<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Event;

use MsgPhp\Domain\Projection\ProjectionDocument;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ProjectionDocumentSavedEvent
{
    public $document;

    final public function __construct(ProjectionDocument $document)
    {
        $this->document = $document;
    }
}
