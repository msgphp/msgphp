<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Entity\Fields;

use MsgPhp\Domain\Entity\Fields\EnabledField;
use PHPUnit\Framework\TestCase;

final class EnabledFieldTest extends TestCase
{
    /**
     * @dataProvider provideStates
     */
    public function testField(bool $state): void
    {
        $object = $this->getObject($state);

        $this->assertSame($state, $object->isEnabled());
    }

    public function provideStates(): iterable
    {
        yield [true];
        yield [false];
    }

    private function getObject($value)
    {
        return new class($value) {
            use EnabledField;

            public function __construct($value)
            {
                $this->enabled = $value;
            }
        };
    }
}
