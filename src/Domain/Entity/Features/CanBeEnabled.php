<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity\Features;

use MsgPhp\Domain\Entity\Fields\EnabledField;
use MsgPhp\Domain\Event\{DisableDomainEvent, EnableDomainEvent};

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

    private function handleEnableEvent(EnableDomainEvent $event): bool
    {
        if ($this->enabled) {
            return false;
        }

        $this->enable();

        return true;
    }

    private function handleDisableEvent(DisableDomainEvent $event): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $this->disable();

        return true;
    }
}
