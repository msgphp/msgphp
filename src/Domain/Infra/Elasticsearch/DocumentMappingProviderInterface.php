<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Elasticsearch;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DocumentMappingProviderInterface
{
    public static function provideDocumentMappings(): iterable;
}
