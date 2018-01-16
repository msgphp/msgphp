<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Entity\Features;

use MsgPhp\Domain\Entity\Features\CanBeEnabled;
use PHPUnit\Framework\TestCase;

final class CanBeEnabledTest extends TestCase
{
    public function testEnable(): void
    {
        $object = $this->getObject(false);

        $this->assertFalse($object->isEnabled());

        $object->enable();

        $this->assertTrue($object->isEnabled());
    }

    public function testDisable(): void
    {
        $object = $this->getObject(true);

        $this->assertTrue($object->isEnabled());

        $object->disable();

        $this->assertFalse($object->isEnabled());
    }

    private function getObject($value)
    {
        return new class($value) {
            use CanBeEnabled;

            public function __construct($value)
            {
                $this->enabled = $value;
            }
        };
    }
}
