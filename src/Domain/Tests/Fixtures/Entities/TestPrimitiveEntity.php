<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

use MsgPhp\Domain\{DomainId, DomainIdInterface};

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestPrimitiveEntity extends BaseTestEntity
{
    /**
     * @var DomainIdInterface
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\Column(type="domain_id")
     */
    public $id;

    public static function getIdFields(): array
    {
        return ['id'];
    }

    public static function getFieldValues(): array
    {
        return [
            'id' => [new DomainId('-1'), new DomainId('0'), new DomainId('1')],
        ];
    }
}
