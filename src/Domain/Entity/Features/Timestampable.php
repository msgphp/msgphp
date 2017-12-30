<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity\Features;

use MsgPhp\Domain\Entity\Fields\{CreatedAtField, LastUpdatedAtField};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait Timestampable
{
    use AbstractUpdated;
    use CreatedAtField;
    use LastUpdatedAtField;

    private function onUpdate(): void
    {
        $this->lastUpdatedAt = new \DateTimeImmutable();
    }
}
