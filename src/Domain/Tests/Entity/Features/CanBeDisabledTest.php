<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Entity\Features;

use MsgPhp\Domain\Entity\Features\CanBeDisabled;
use PHPUnit\Framework\TestCase;

final class CanBeDisabledTest extends TestCase
{
    public function testObject(): void
    {
        $object = new class() {
            use CanBeDisabled;
        };

        $this->assertTrue($object->isEnabled());
    }
}
