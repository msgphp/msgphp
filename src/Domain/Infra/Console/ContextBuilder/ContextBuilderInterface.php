<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\ContextBuilder;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface ContextBuilderInterface
{
    public function configure(InputDefinition $definition): void;

    public function getContext(InputInterface $input, StyleInterface $io, array $values = []): array;
}
