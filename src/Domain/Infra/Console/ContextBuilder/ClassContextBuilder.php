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
    public const ALWAYS_OPTIONAL = 1;
    public const NO_DEFAULTS = 2;

    private $class;
    private $method;
    private $elementProviders;
    private $classMapping;
    private $flags = 0;
    private $resolved;
    private $fieldMapping = [];
    private $generatedValues = [];

    /**
     * @param ContextElementProviderInterface[] $elementProviders
     */
    public function __construct(string $class, string $method, iterable $elementProviders = [], array $classMapping = [], int $flags = 0)
    {
        $this->class = $class;
        $this->method = $method;
        $this->elementProviders = $elementProviders;
        $this->classMapping = $classMapping;
        $this->flags = $flags;
    }

    public function configure(InputDefinition $definition): void
    {
        $known = array_flip(array_keys($definition->getOptions() + $definition->getArguments()));

        foreach ($this->resolve() as $argument) {
            $required = false;
            if ('bool' === $argument['type']) {
                $mode = InputOption::VALUE_NONE;
            } elseif (self::isComplex($argument['type'])) {
                $mode = InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY;
            } elseif (!$argument['required'] || ($this->flags & self::ALWAYS_OPTIONAL)) {
                $mode = InputOption::VALUE_OPTIONAL;
            } else {
                $mode = InputArgument::OPTIONAL;
                $required = true;
            }

            $i = 1;
            $field = $key = str_replace('_', '-', $argument['key']);
            while (isset($known[$field])) {
                $field = $key.++$i;
            }

            if ($required) {
                $definition->addArgument(new InputArgument($field, $mode, $argument['element']->description));
            } else {
                $definition->addOption(new InputOption($field, null, $mode, $argument['element']->description));
            }

            $this->fieldMapping[$argument['name']] = [$field, $required];
        }
    }

    public function getContext(InputInterface $input, StyleInterface $io, iterable $resolved = null): array
    {
        $context = $normalizers = [];
        $interactive = $input->isInteractive();

        foreach ($resolved ?? $this->resolve() as $argument) {
            $key = $argument['name'];
            if (null === $resolved) {
                list($field, $isArg) = $this->fieldMapping[$key];
                $value = $isArg ? $input->getArgument($field) : $input->getOption($field);
            } else {
                $field = $key;
                $value = $argument['value'] ?? null;
            }

            /** @var ContextElement $element */
            $element = $argument['element'];
            if (null !== $element->normalizer) {
                $normalizers[$key] = $element->normalizer;
            }

            $given = $value || $input->hasParameterOption('--'.$field);
            $required = $argument['required'] && !($this->flags & self::ALWAYS_OPTIONAL);

            if (self::isObject($type = $argument['type']) && ($required || $given)) {
                $method = is_subclass_of($type, DomainCollectionInterface::class) || is_subclass_of($type, DomainIdInterface::class) ? 'fromValue' : '__construct';
                $context[$key] = $this->getContext($input, $io, array_map(function (array $argument, int $i) use ($type, $method, $value, $element): array {
                    if (array_key_exists($i, $value)) {
                        $argument['value'] = $value[$i];
                    } elseif ('bool' === $argument['type']) {
                        $argument['value'] = false;
                    } elseif (self::isComplex($argument['type'])) {
                        $argument['value'] = [];
                    }

                    $child = $this->getElement($type, $method, $argument['name']);
                    $child->label = $element->label.' > '.$child->label;

                    return ['element' => $child] + $argument;
                }, $objectResolved = ClassMethodResolver::resolve($type, $method), array_keys($objectResolved)));
                continue;
            }

            if (null !== $value && false !== $value && [] !== $value) {
                $context[$key] = $value;
                continue;
            }

            if ($required || $given) {
                if (!$interactive) {
                    throw new \LogicException(sprintf('No value provided for "%s".', $field));
                }

                $context[$key] = $this->askRequiredValue($io, $element, $value);
                continue;
            }

            if ($this->flags & self::NO_DEFAULTS) {
                unset($normalizers[$key]);
                continue;
            }

            if ($this->generatedValue($element, $generated)) {
                $context[$key] = $generated;
                continue;
            }

            $context[$key] = $argument['default'];
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
            $this->resolved[] = [
                'element' => $this->getElement($this->class, $this->method, $argument['name']),
                'type' => isset($argument['type']) ? ($this->classMapping[$argument['type']] ?? $argument['type']) : null,
            ] + $argument;
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

        return new ContextElement(ucfirst(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1 \\2', '\\1 \\2'), $argument)));
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
