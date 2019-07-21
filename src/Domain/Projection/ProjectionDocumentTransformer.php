<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface ProjectionDocumentTransformer
{
    public function transform(object $object): array;
}
