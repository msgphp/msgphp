<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\GenericDomainId;
use MsgPhp\Domain\Tests\Fixtures\TestDomainId;

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestEntity extends BaseTestEntity
{
    /**
     * @var null|string
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    public $strField;
    /**
     * @var int
     * @Doctrine\ORM\Mapping\Column(type="integer", nullable=false)
     */
    public $intField;
    /**
     * @var null|float
     * @Doctrine\ORM\Mapping\Column(type="float", nullable=true)
     */
    public $floatField;
    /**
     * @var bool
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=false)
     */
    public $boolField;
    /**
     * @var null|int
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\GeneratedValue()
     * @Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    public static function getIdFields(): array
    {
        return ['id'];
    }

    public static function getFieldValues(): array
    {
        return [
            'strField' => [null, '', 'foo'],
            'intField' => [0, 1],
            'floatField' => [null, .0, -1.23],
            'boolField' => [true, false],
        ];
    }

    public function setId(DomainId $id): void
    {
        $this->id = $id->isNil() ? null : (int) $id->toString();
    }

    public function getId(): DomainId
    {
        return GenericDomainId::fromInt($this->id);
    }
}
