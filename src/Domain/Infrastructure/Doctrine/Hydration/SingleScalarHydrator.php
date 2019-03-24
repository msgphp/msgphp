<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Doctrine\Hydration;

use Doctrine\ORM\Internal\Hydration\SingleScalarHydrator as BaseSingleScalarHydrator;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainIdType;

final class SingleScalarHydrator extends BaseSingleScalarHydrator
{
    public const NAME = 'msgphp_single_scalar';

    protected function gatherScalarRowData(&$data): array
    {
        array_walk($data, function (&$value, $key): void {
            if (($info = $this->hydrateColumnInfo($key)) && isset($info['type']) && $info['type'] instanceof DomainIdType) {
                $value = DomainIdType::getType($info['type']::getDataType())->convertToPHPValue($value, $this->_platform);
            }

            unset($value);
        });

        return parent::gatherScalarRowData($data);
    }
}
