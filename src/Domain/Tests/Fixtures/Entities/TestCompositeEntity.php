<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

use MsgPhp\Domain\{DomainId, DomainIdInterface};

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestCompositeEntity extends BaseTestEntity
{
    /**
     * @var DomainIdInterface
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\Column(type="domain_id")
     */
    public $idA;

    /**
     * @var string
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\Column(type="string")
     */
    public $idB;

    public static function getIdFields(): array
    {
        return ['idA', 'idB'];
    }

    public static function getFieldValues(): array
    {
        return [
            'idA' => [new DomainId('-1'), new DomainId('0'), new DomainId('1')],
            'idB' => ['', 'foo'],
        ];
    }
}
