<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

use MsgPhp\Domain\DomainId;

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestDerivedCompositeEntity extends BaseTestEntity
{
    /**
     * @var TestPrimitiveEntity
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="TestPrimitiveEntity", cascade={"all"})
     */
    public $entity;

    /**
     * @var int
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\Column(type="integer")
     */
    public $id;

    public static function getIdFields(): array
    {
        return ['entity', 'id'];
    }

    public static function getFieldValues(): array
    {
        static $entity;
        if (null === $entity) {
            $entity = TestPrimitiveEntity::create(['id' => new DomainId('999')]);
        }

        return [
            'entity' => [$entity],
            'id' => [0, 1],
        ];
    }
}
