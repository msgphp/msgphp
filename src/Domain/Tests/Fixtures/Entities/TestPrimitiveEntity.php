<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Tests\Fixtures\TestDomainId;

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestPrimitiveEntity extends BaseTestEntity
{
    /**
     * @var DomainIdInterface
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\Column(type="msgphp_domain_id")
     */
    public $id;

    public static function getIdFields(): array
    {
        return ['id'];
    }

    public static function getFieldValues(): array
    {
        return [
            'id' => [new TestDomainId('-1'), new TestDomainId('0'), new TestDomainId('1')],
        ];
    }
}
