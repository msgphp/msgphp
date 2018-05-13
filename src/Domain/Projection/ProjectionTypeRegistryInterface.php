<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface ProjectionTypeRegistryInterface
{
    /**
     * @return string[]
     */
    public function all(): array;

    public function initialize(): void;

    public function destroy(): void;
}
