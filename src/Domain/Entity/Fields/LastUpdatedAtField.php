<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity\Fields;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait LastUpdatedAtField
{
    private $lastUpdatedAt;

    public function getLastUpdatedAt(): \DateTimeInterface
    {
        return $this->lastUpdatedAt;
    }
}
