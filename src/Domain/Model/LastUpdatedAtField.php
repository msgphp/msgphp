<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Model;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait LastUpdatedAtField
{
    /**
     * @var \DateTimeInterface
     */
    private $lastUpdatedAt;

    public function getLastUpdatedAt(): \DateTimeInterface
    {
        return $this->lastUpdatedAt;
    }
}
