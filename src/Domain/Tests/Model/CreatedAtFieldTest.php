<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Model;

use MsgPhp\Domain\Model\CreatedAtField;
use PHPUnit\Framework\TestCase;

final class CreatedAtFieldTest extends TestCase
{
    public function testField(): void
    {
        $object = $this->getObject($value = new \DateTime());

        self::assertSame($value, $object->getCreatedAt());
    }

    /**
     * @return object
     */
    private function getObject($value)
    {
        return new class($value) {
            use CreatedAtField;

            public function __construct($value)
            {
                $this->createdAt = $value;
            }
        };
    }
}
