<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestPrimitiveEntity extends BaseTestEntity
{
    /**
     * @var string
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\Column(type="string")
     */
    public $id;

    public static function getIdFields(): array
    {
        return ['id'];
    }

    public static function getFieldValues(): array
    {
        return [
            'id' => ['-1', '0', '1'],
        ];
    }
}
