<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection\Event;

use MsgPhp\Domain\Projection\ProjectionDocument;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ProjectionDocumentDeletedEvent
{
    /**
     * @var ProjectionDocument
     */
    public $document;

    final public function __construct(ProjectionDocument $document)
    {
        $this->document = $document;
    }
}
