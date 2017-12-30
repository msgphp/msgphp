<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Entity\Features;

use MsgPhp\Domain\Entity\Features\Timestampable;
use PHPUnit\Framework\TestCase;

final class TimestampableTest extends TestCase
{
    public function testObject(): void
    {
        $object = new class() {
            use Timestampable { onUpdate as public; }
        };

        $now = new \DateTime();
        $object->onUpdate();

        $this->assertGreaterThanOrEqual($now, $object->getLastUpdatedAt());
    }
}
