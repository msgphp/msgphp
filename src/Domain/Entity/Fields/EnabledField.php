<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity\Fields;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EnabledField
{
    /** @var bool */
    private $enabled = false;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
