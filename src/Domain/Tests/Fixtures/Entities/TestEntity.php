<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures\Entities;

use MsgPhp\Domain\{DomainId, DomainIdInterface};

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestEntity extends BaseTestEntity
{
    /**
     * @var DomainIdInterface|null
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\GeneratedValue()
     * @Doctrine\ORM\Mapping\Column(type="domain_id")
     */
    private $id;

    /**
     * @var string|null
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    public $strField;

    /**
     * @var int
     * @Doctrine\ORM\Mapping\Column(type="integer", nullable=false)
     */
    public $intField;

    /**
     * @var float|null
     * @Doctrine\ORM\Mapping\Column(type="float", nullable=true)
     */
    public $floatField;

    /**
     * @var bool
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=false)
     */
    public $boolField;

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

    final public function identify(DomainIdInterface $id): void
    {
        $this->id = $id;
    }

    final public function getId(): DomainIdInterface
    {
        return $this->id ?? ($this->id = new DomainId());
    }
}
