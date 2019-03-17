<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Model;

use MsgPhp\Domain\Model\LastUpdatedAtField;
use PHPUnit\Framework\TestCase;

final class LastUpdatedAtFieldTest extends TestCase
{
    public function testField(): void
    {
        $object = $this->getObject($value = new \DateTime());

        self::assertSame($value, $object->getLastUpdatedAt());
    }

    /**
     * @return object
     */
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
