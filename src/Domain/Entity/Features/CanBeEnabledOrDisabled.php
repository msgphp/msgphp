<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity\Features;

use MsgPhp\Domain\Entity\Fields\EnabledField;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait CanBeEnabledOrDisabled
{
    use AbstractUpdated;
    use EnabledField;

    public function enable(): void
    {
        $this->enabled = true;

        $this->onUpdate();
    }

    public function disable(): void
    {
        $this->enabled = false;

        $this->onUpdate();
    }
}
