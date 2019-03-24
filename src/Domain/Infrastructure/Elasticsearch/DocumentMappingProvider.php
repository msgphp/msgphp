<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Elasticsearch;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DocumentMappingProvider
{
    public static function provideDocumentMappings(): iterable;
}
