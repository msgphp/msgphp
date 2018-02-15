<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Entity\Features;

use MsgPhp\Domain\Entity\Features\CanBeEnabled;
use MsgPhp\Domain\Event\{DisableEvent, EnableEvent};
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

    public function testHandleEnableEvent(): void
    {
        $object = $this->getObject(false);

        $this->assertTrue($object->handleEnableEvent($this->createMock(EnableEvent::class)));
        $this->assertTrue($object->isEnabled());
        $this->assertFalse($object->handleEnableEvent($this->createMock(EnableEvent::class)));
        $this->assertTrue($object->isEnabled());
    }

    public function testHandleDisableEvent(): void
    {
        $object = $this->getObject(true);

        $this->assertTrue($object->handleDisableEvent($this->createMock(DisableEvent::class)));
        $this->assertFalse($object->isEnabled());
        $this->assertFalse($object->handleDisableEvent($this->createMock(DisableEvent::class)));
        $this->assertFalse($object->isEnabled());
    }

    private function getObject($value)
    {
        return new class($value) {
            use CanBeEnabled {
                handleDisableEvent as public;
                handleEnableEvent as public;
            }

            public function __construct($value)
            {
                $this->enabled = $value;
            }
        };
    }
}
