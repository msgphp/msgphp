<?php

declare(strict_types=1);

/*
 * This file is part of the MsgPHP package.
 *
 * (c) Roland Franssen <franssen.roland@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DomainIdInterface
{
    /**
     * @param mixed $value
     */
    public static function fromValue($value): self;

    public function isEmpty(): bool;

    public function equals(self $id): bool;

    public function toString(): string;

    public function __toString(): string;
}
