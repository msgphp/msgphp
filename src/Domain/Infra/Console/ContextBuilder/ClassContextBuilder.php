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
    private $method;
    private $elementProviders;
    private $classMapping;
    private $resolved;
    private $isOption = [];
    private $generatedValues = [];

    /**
     * @param ContextElementProviderInterface[] $elementProviders
     */
    public function __construct(string $class, string $method, iterable $elementProviders = [], array $classMapping = [])
    {
        $this->class = $class;
        $this->method = $method;
        $this->elementProviders = $elementProviders;
        $this->classMapping = $classMapping;
    }

    public function configure(InputDefinition $definition): void
    {
        foreach ($this->resolve() as $argument) {
            $field = $argument['field'];
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
                $definition->addOption(new InputOption($field, null, $mode, $argument['element']->description));
            } else {
                $definition->addArgument(new InputArgument($field, $mode, $argument['element']->description));
            }
        }
    }

    public function getContext(InputInterface $input, StyleInterface $io, iterable $resolved = null): array
    {
        $context = $normalizers = [];
        $interactive = $input->isInteractive();

        foreach ($resolved ?? $this->resolve() as $argument) {
            $key = $argument['name'];
            $field = $argument['field'] ?? $key;
            $value = null === $resolved
                ? ($this->isOption[$field] ? $input->getOption($field) : $input->getArgument($field))
                : ($argument['value'] ?? null);

            /** @var ContextElement $element */
            $element = $argument['element'];

            if (null !== $element->normalizer) {
                $normalizers[$key] = $element->normalizer;
            }

            if (self::isObject($type = $argument['type'])) {
                if ($this->generatedValue($element, $generated)) {
                    $context[$key] = $generated;
                    continue;
                }

                $class = $this->classMapping[$type] ?? $type;
                $method = is_subclass_of($class, DomainCollectionInterface::class) || is_subclass_of($class, DomainIdInterface::class) ? 'fromValue' : '__construct';
                $context[$key] = $this->getContext($input, $io, array_map(function (array $argument, int $i) use ($class, $method, $value, $element): array {
                    if (array_key_exists($i, $value)) {
                        $argument['value'] = $value[$i];
                    } elseif ('bool' === $argument['type']) {
                        $argument['value'] = false;
                    } elseif (self::isComplex($argument['type'])) {
                        $argument['value'] = [];
                    }

                    $child = $this->getElement($class, $method, $argument['name']);
                    $child->label = $element->label.' > '.$child->label;

                    return ['element' => $child] + $argument;
                }, $objectResolved = ClassMethodResolver::resolve($class, $method), array_keys($objectResolved)));
                continue;
            }

            if (null !== $value && false !== $value && [] !== $value) {
                $context[$key] = $value;
                continue;
            }

            if (!$argument['required']) {
                $context[$key] = $this->generatedValue($element, $generated) ? $generated : $argument['default'];
                continue;
            }

            if (!$interactive) {
                if ($this->generatedValue($element, $generated)) {
                    $context[$key] = $generated;
                    continue;
                }

                throw new \LogicException(sprintf('No value provided for "%s".', $field));
            }

            $context[$key] = $this->askRequiredValue($io, $element, $value);
        }

        foreach ($normalizers as $key => $normalizer) {
            $context[$key] = $normalizer($context[$key], $context);
        }

        $generatedValues = [];
        while (null !== $generatedValue = array_shift($this->generatedValues)) {
            list($label, $value) = $generatedValue;

            $generatedValues[] = [$label, json_encode($value)];
        }
        if ($generatedValues) {
            $io->note('Generated values');
            $io->table([], $generatedValues);
        }

        return $context;
    }

    private static function isComplex(?string $type): bool
    {
        return 'array' === $type || 'iterable' === $type || self::isObject($type);
    }

    private static function isObject(?string $type): bool
    {
        return null !== $type && (class_exists($type) || interface_exists($type));
    }

    private function resolve(): iterable
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

            $this->resolved[$field] = ['field' => $field, 'element' => $this->getElement($this->class, $this->method, $argument['name'])] + $argument;
        }

        return $this->resolved;
    }

    private function getElement(string $class, string $method, string $argument): ContextElement
    {
        foreach ($this->elementProviders as $provider) {
            if (null !== $element = $provider->getElement($class, $method, $argument)) {
                return $element;
            }
        }

        return new ContextElement(str_replace('_', ' ', ucfirst($argument)));
    }

    private function askRequiredValue(StyleInterface $io, ContextElement $element, $default)
    {
        $label = $element->label;
        $generated = null !== $element->generator;

        if (null === $default) {
            if ($generated) {
                $label .= ' (leave blank to generate a value)';
            }

            do {
                if (null === $value = $element->hidden ? $io->askHidden($label) : $io->ask($label)) {
                    $this->generatedValue($element, $value);
                }
            } while (!$generated && null === $value);

            return $value;
        }

        if ($generated && $io->confirm(sprintf('Generate value for "%s"?', $label))) {
            $this->generatedValue($element, $value);

            return $value;
        }

        if (false === $default) {
            return $io->confirm($label, false);
        }

        if ([] === $default) {
            $i = 0;
            $value = [];
            do {
                $offsetLabel = $label.' ['.$i.']';
                $value[] = $element->hidden ? $io->askHidden($offsetLabel) : $io->ask($offsetLabel);
                ++$i;
            } while ($io->confirm('Add another value?', false));

            return $value;
        }

        return $default;
    }

    private function generatedValue(ContextElement $element, &$generated): bool
    {
        if (null === $element->generator) {
            $generated = null;
            $result = false;
        } else {
            $generated = ($element->generator)();
            $result = true;

            $this->generatedValues[] = [$element->label, $generated];
        }

        unset($generated);

        return $result;
    }
}
