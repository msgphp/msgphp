<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface ProjectionTypeRegistry
{
    public function initialize(string ...$type): void;

    public function destroy(string ...$type): void;

    public function lookup(string $name): string;
}
