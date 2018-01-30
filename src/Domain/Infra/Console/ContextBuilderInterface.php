<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface ContextBuilderInterface
{
    /**
     * @return InputOption[]
     */
    public function getOptions(): iterable;

    /**
     * @return InputArgument[]
     */
    public function getArguments(): iterable;

    public function getContext(InputInterface $input, StyleInterface $io): array;
}
