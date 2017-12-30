<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Entity\Features;

use MsgPhp\Domain\Entity\Features\AbstractUpdated;
use PHPUnit\Framework\TestCase;

final class AbstractUpdatedTest extends TestCase
{
    public function testObject(): void
    {
        $object = new class() {
            use TestAbstractUpdatedFeature;

            public $updated = false;

            private function onUpdate(): void
            {
                $this->updated = true;
            }
        };

        $object->update();

        $this->assertTrue($object->updated);
    }
}

trait TestAbstractUpdatedFeature
{
    use AbstractUpdated;

    public function update(): void
    {
        $this->onUpdate();
    }

    private function onUpdate(): void
    {
    }
}
