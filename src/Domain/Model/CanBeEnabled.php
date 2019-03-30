<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Model;

use MsgPhp\Domain\Event\Disable;
use MsgPhp\Domain\Event\Enable;

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

    private function onEnableEvent(Enable $event): bool
    {
        if (!$this->enabled) {
            $this->enable();

            return true;
        }

        return false;
    }

    private function onDisableEvent(Disable $event): bool
    {
        if ($this->enabled) {
            $this->disable();

            return true;
        }

        return false;
    }
}
