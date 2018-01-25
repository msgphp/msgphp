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

    private function handleEnableEvent(EnableDomainEvent $event): void
    {
        $this->enable();
    }

    private function handleDisableEvent(DisableDomainEvent $event): void
    {
        $this->disable();
    }
}
