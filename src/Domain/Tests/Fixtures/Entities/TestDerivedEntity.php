<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

use MsgPhp\Domain\DomainId;

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestDerivedEntity extends BaseTestEntity
{
    /**
     * @var TestEntity
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\OneToOne(targetEntity="TestEntity", cascade={"persist"})
     */
    public $entity;

    public static function getIdFields(): array
    {
        return ['entity'];
    }

    public static function getFieldValues(): array
    {
        return [
            'entity' => [TestEntity::create(['id' => new DomainId('1'), 'intField' => 0, 'boolField' => true])],
        ];
    }
}
