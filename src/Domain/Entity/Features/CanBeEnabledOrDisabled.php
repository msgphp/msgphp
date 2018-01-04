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

    final public function enable(): void
    {
        $this->enabled = true;

        $this->onUpdate();
    }

    final public function disable(): void
    {
        $this->enabled = false;

        $this->onUpdate();
    }
}
