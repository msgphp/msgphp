<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity\Features;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
trait AbstractEnabledDisabled
{
    use AbstractUpdated;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isDisabled(): bool
    {
        return !$this->enabled;
    }

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
