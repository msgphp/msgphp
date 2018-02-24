<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestChildEntity extends TestParentEntity
{
    /**
     * @var string|null
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    public $childField;

    public static function getFieldValues(): array
    {
        return parent::getFieldValues() + [
            'childField' => [null, 'bar'],
        ];
    }
}
