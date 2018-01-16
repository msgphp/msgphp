<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity\Features;

use MsgPhp\Domain\Entity\Fields\EnabledField;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait CanBeEnabled
{
    use EnabledField;

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }
}
