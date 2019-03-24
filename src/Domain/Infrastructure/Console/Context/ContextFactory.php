<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Console\Context;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface ContextFactory
{
    public function configure(InputDefinition $definition): void;

    public function getContext(InputInterface $input, StyleInterface $io, array $values = []): array;
}
