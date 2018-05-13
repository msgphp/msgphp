<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Event;

use MsgPhp\Domain\Projection\DomainProjectionDocument;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DomainProjectionDocumentDeletedEvent
{
    public $document;

    final public function __construct(DomainProjectionDocument $document)
    {
        $this->document = $document;
    }
}
