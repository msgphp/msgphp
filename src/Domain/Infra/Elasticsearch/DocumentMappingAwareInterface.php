<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Elasticsearch;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DocumentMappingAwareInterface
{
    public static function getDocumentMapping(): array;
}
