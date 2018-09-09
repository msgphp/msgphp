<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Entity\Fields;

use MsgPhp\Domain\Entity\Fields\LastUpdatedAtField;
use PHPUnit\Framework\TestCase;

final class LastUpdatedAtFieldTest extends TestCase
{
    public function testField(): void
    {
        $object = $this->getObject($value = new \DateTime());

        self::assertSame($value, $object->getLastUpdatedAt());
    }

    private function getObject($value)
    {
        return new class($value) {
            use LastUpdatedAtField;

            public function __construct($value)
            {
                $this->lastUpdatedAt = $value;
            }
        };
    }
}
