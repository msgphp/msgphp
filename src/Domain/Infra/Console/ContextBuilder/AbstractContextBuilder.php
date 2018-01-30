<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\ContextBuilder;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class AbstractContextBuilder implements ContextBuilderInterface
{
    public function configure(InputDefinition $definition): void
    {
        foreach ($this->getOptions() as $option) {
            $definition->addOption($option);
        }

        foreach ($this->getArguments() as $argument) {
            $definition->addArgument($argument);
        }
    }

    public function getContext(InputInterface $input, StyleInterface $io): array
    {
        return [];
    }

    /**
     * @return InputArgument[]
     */
    abstract protected function getArguments(): iterable;

    /**
     * @return InputOption[]
     */
    abstract protected function getOptions(): iterable;
}
