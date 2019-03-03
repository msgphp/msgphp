<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity\Features;

use MsgPhp\Domain\Event\DisableEvent;
use MsgPhp\Domain\Event\EnableEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait CanBeEnabled
{
    /**
     * @var bool
     */
    private $enabled = false;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    private function handleEnableEvent(EnableEvent $event): bool
    {
        if (!$this->enabled) {
            $this->enable();

            return true;
        }

        return false;
    }

    private function handleDisableEvent(DisableEvent $event): bool
    {
        if ($this->enabled) {
            $this->disable();

            return true;
        }

        return false;
    }
}
