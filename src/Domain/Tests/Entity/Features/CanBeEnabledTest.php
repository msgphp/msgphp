<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Entity\Features;

use MsgPhp\Domain\Entity\Features\CanBeEnabled;
use PHPUnit\Framework\TestCase;

final class CanBeEnabledTest extends TestCase
{
    public function testObject(): void
    {
        $object = new class() {
            use CanBeEnabled;
        };

        $this->assertFalse($object->isEnabled());
    }
}
