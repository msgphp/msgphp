<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Command;

use MsgPhp\Domain\Projection\DomainProjectionDocument;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class SaveDomainProjectionDocumentCommand
{
    public $type;
    public $id;
    public $body;

    final public function __construct(DomainProjectionDocument $document)
    {
        $this->type = $document->getType();
        $this->id = $document->getId();
        $this->body = $document->getBody();
    }
}
