<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Command;

use MsgPhp\Domain\Projection\ProjectionDocument;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class SaveProjectionDocumentCommand
{
    public $type;
    public $id;
    public $body;

    final public function __construct(ProjectionDocument $document)
    {
        $this->type = $document->getType();
        $this->id = $document->getId();
        $this->body = $document->getBody();
    }
}
