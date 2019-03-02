<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

use MsgPhp\Domain\Tests\Fixtures\TestDomainId;

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestDerivedEntity extends BaseTestEntity
{
    /**
     * @var TestEntity
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\OneToOne(targetEntity="TestEntity", cascade={"all"})
     */
    public $entity;

    public static function getIdFields(): array
    {
        return ['entity'];
    }

    public static function getFieldValues(): array
    {
        static $entity;
        if (null === $entity) {
            $entity = TestEntity::create(['intField' => 0, 'boolField' => true]);

            // https://github.com/doctrine/doctrine2/issues/4584
            $entity->setId(new TestDomainId('IRRELEVANT'));
        }

        return [
            'entity' => [$entity],
        ];
    }
}
