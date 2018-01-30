<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\ContextBuilder;

use MsgPhp\Domain\{DomainCollectionInterface, DomainIdInterface};
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
    private $classMapping;
    private $method;
    private $resolved;
    private $isOption = [];

    public function __construct(string $class, array $classMapping = [], string $method = '__construct')
    {
        $this->class = $class;
        $this->classMapping = $classMapping;
        $this->method = $method;
    }

    public function configure(InputDefinition $definition): void
    {
        foreach ($this->resolve() as $field => $argument) {
            $this->isOption[$field] = true;
            if ('bool' === $argument['type']) {
                $mode = InputOption::VALUE_NONE;
            } elseif (self::isComplex($argument['type'])) {
                $mode = InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY;
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

    public function getContext(InputInterface $input, StyleInterface $io, array $resolved = null): array
    {
        $context = [];
        $interactive = $input->isInteractive();

        foreach ($resolved ?? $this->resolve() as $field => $argument) {
            $value = null === $resolved ? ($this->isOption[$field] ? $input->getOption($field) : $input->getArgument($field)) : $argument['default'];

            if ($argument['required']) {
                $label = str_replace('_', ' ', ucfirst($argument['name']));
                if (isset($argument['label'])) {
                    $label = sprintf($argument['label'], $label);
                }

                if (false === $value) {
                    if (!$interactive) {
                        throw new \LogicException(sprintf('No value provided for "%s".', $field));
                    }

                    $value = $io->confirm($label, false);
                } elseif (null === $value) {
                    if (!$interactive) {
                        throw new \LogicException(sprintf('No value provided for "%s".', $field));
                    }

                    do {
                        $value = $io->ask($label);
                    } while (null === $value);
                } elseif ([] === $value) {
                    if (self::isObject($type = $argument['type'])) {
                        $value = $this->getContext($input, $io, array_map(function (array $argument) use ($label): array {
                            if ('bool' === $argument['type']) {
                                $argument['default'] = false;
                            } elseif (self::isComplex($argument['type'])) {
                                $argument['default'] = [];
                            }

                            return ['label' => $label.' > %s'] + $argument;
                        }, ClassMethodResolver::resolve(
                            $class = $this->classMapping[$type] ?? $type,
                            is_subclass_of($class, DomainCollectionInterface::class) || is_subclass_of($class, DomainIdInterface::class) ? 'fromValue' : '__construct'
                        )));
                    } else {
                        if (!$interactive) {
                            throw new \LogicException(sprintf('No value provided for "%s".', $field));
                        }

                        $i = 1;
                        do {
                            $value[] = $io->ask($label.(1 < $i ? ' ('.$i.')' : ''));
                            ++$i;
                        } while ($io->confirm('Add another value?', false));
                    }
                }
            } elseif (false === $value) {
                $value = null;
            }

            $context[$argument['name']] = $value;
        }

        return $context;
    }

    private function resolve(): array
    {
        if (null !== $this->resolved) {
            return $this->resolved;
        }

        $this->resolved = [];

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

    private static function isComplex(?string $type): bool
    {
        return 'array' === $type || 'iterable' === $type || self::isObject($type);
    }

    private static function isObject(?string $type): bool
    {
        return null !== $type && (class_exists($type) || interface_exists($type));
    }
}
