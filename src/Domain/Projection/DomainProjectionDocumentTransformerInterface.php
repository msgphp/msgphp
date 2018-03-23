<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DomainProjectionDocumentTransformerInterface
{
    /**
     * @param object $object
     */
    public function transform($object): DomainProjectionDocument;
}
