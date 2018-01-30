<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\ContextBuilder;

use MsgPhp\Domain\Factory\ClassMethodResolver;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ClassContextBuilder implements ContextBuilderInterface
{
    private $class;
    private $method;
    private $resolved;
    private $isOption = [];

    public function __construct(string $class, string $method = '__construct')
    {
        $this->class = $class;
        $this->method = $method;
    }

    public function configure(InputDefinition $definition): void
    {
        foreach ($this->resolve() as $field => $argument) {
            $this->isOption[$field] = true;
            if (!isset($argument['type'])) {
                $mode = InputOption::VALUE_OPTIONAL;
            } elseif ('array' === $argument['type'] || 'iterable' === $argument['type'] || class_exists($argument['type']) || interface_exists($argument['type'])) {
                $mode = InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY;
            } elseif ('bool' === $argument['type']) {
                $mode = InputOption::VALUE_NONE;
            } elseif (!$argument['required']) {
                $mode = InputOption::VALUE_OPTIONAL;
            } else {
                $mode = InputArgument::OPTIONAL;
                $this->isOption[$field] = false;
            }

            if ($this->isOption[$field]) {
                $definition->addOption(new InputOption($field, null, $mode));
            } else {
                $definition->addArgument(new InputArgument($field, $mode));
            }
        }
    }

    public function getContext(InputInterface $input, StyleInterface $io): array
    {
        $context = [];
        $interactive = $input->isInteractive();

        foreach ($this->resolve() as $field => $argument) {
            $value = $this->isOption[$field] ? $input->getOption($field) : $input->getArgument($field);

            if ($argument['required']) {
                if (!$interactive) {
                    throw new \LogicException(sprintf('No value provided for "%s".', $field));
                }

                $label = str_replace('_', ' ', ucfirst($field));

                if (false === $value) {
                    $value = $io->confirm($label, false);
                } elseif (is_array($value)) {
                    $i = 1;
                    do {
                        $value[] = $io->ask($label.(1 < $i ? ' ('.$i.')' : ''));
                        ++$i;
                    } while ($io->confirm('Add another value?', false));
                } elseif (null === $value) {
                    do {
                        $value = $io->ask($label);
                    } while (null === $value);
                }
            } elseif (false === $value) {
                $value = null;
            }

            $context[$field] = $value;
        }

        return $context;
    }

    private function resolve(): array
    {
        if (null !== $this->resolved) {
            return $this->resolved;
        }

        $this->resolved = $counts = [];

        foreach (ClassMethodResolver::resolve($this->class, $this->method) as $argument) {
            $field = $argument['key'];
            $i = 0;
            while (isset($this->resolved[$field])) {
                $field = $argument['key'].++$i;
            }

            $this->resolved[$field] = $argument;
        }

        return $this->resolved;
    }
}
