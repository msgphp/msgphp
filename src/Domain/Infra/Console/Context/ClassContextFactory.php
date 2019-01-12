<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\Context;

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
final class ClassContextFactory implements ContextFactoryInterface
{
    public const ALWAYS_OPTIONAL = 1;
    public const NO_DEFAULTS = 2;
    public const REUSE_DEFINITION = 4;

    private $class;
    private $method;
    private $classMapping;
    private $flags = 0;
    private $elementFactory;
    private $resolved;
    private $fieldMapping = [];
    private $generatedValues = [];

    public static function getUniqueFieldName(InputDefinition $definition, string $field, bool $isOption = true): string
    {
        $known = $isOption ? $definition->getOptions() : $definition->getArguments();
        $base = $field;
        $i = 1;
        while (isset($known[$field])) {
            $field = $base.++$i;
        }

        return $field;
    }

    public function __construct(string $class, string $method, array $classMapping = [], int $flags = 0, ClassContextElementFactoryInterface $elementFactory = null)
    {
        $this->class = $class;
        $this->method = $method;
        $this->classMapping = $classMapping;
        $this->flags = $flags;
        $this->elementFactory = $elementFactory ?? new ClassContextElementFactory();
    }

    public function configure(InputDefinition $definition): void
    {
        $this->fieldMapping = [];

        if ($this->flags & self::REUSE_DEFINITION) {
            $origOptions = $definition->getOptions();
            $origArgs = $definition->getArguments();
        } else {
            $origOptions = $origArgs = [];
        }

        foreach ($this->resolve() as $argument) {
            $isOption = true;
            if ('bool' === $argument['type']) {
                $mode = InputOption::VALUE_NONE;
            } elseif (self::isComplex($argument['type'])) {
                $mode = InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY;
            } elseif (!$argument['required'] || ($this->flags & self::ALWAYS_OPTIONAL)) {
                $mode = InputOption::VALUE_OPTIONAL;
            } else {
                $mode = InputArgument::OPTIONAL;
                $isOption = false;
            }

            $field = $isOption ? str_replace('_', '-', $argument['key']) : $argument['key'];
            if (!isset($origOptions[$field]) && !isset($origArgs[$field])) {
                $field = self::getUniqueFieldName($definition, $field, $isOption);

                if ($isOption) {
                    $definition->addOption(new InputOption($field, null, $mode, $argument['element']->description));
                } else {
                    $definition->addArgument(new InputArgument($field, $mode, $argument['element']->description));
                }
            } else {
                $isOption = isset($origOptions[$field]);
            }

            $this->fieldMapping[$argument['name']] = [$field, $isOption];
        }
    }

    public function getContext(InputInterface $input, StyleInterface $io, array $values = [], iterable $resolved = null): array
    {
        $context = $normalizers = [];
        $interactive = $input->isInteractive();

        foreach ($resolved ?? $this->resolve() as $argument) {
            $key = $argument['name'];
            if (null === $resolved) {
                [$field, $isOption] = $this->fieldMapping[$key];
                $value = $isOption ? $input->getOption($field) : $input->getArgument($field);
            } else {
                $field = $key;
                $value = $argument['value'] ?? null;
            }

            if (array_key_exists($key, $values)) {
                $context[$key] = $values[$key];
                continue;
            }

            $isEmpty = null === $value || false === $value || [] === $value;
            $given = !$isEmpty || $input->hasParameterOption('--'.$field);

            /** @var ContextElement $element */
            $element = $argument['element'];
            $required = $argument['required'] && !($this->flags & self::ALWAYS_OPTIONAL);

            if (\is_array($value) && self::isObject($type = $argument['type']) && ($required || $given)) {
                $method = is_subclass_of($type, DomainCollectionInterface::class) || is_subclass_of($type, DomainIdInterface::class) ? 'fromValue' : '__construct';
                $context[$key] = $this->getContext($input, $io, [], array_map(function (array $argument, int $i) use ($type, $method, $value, $element): array {
                    if (array_key_exists($argument['name'], $value)) {
                        $argument['value'] = $value[$argument['name']];
                    } elseif (array_key_exists($i, $value)) {
                        $argument['value'] = $value[$i];
                    } elseif ('bool' === $argument['type']) {
                        $argument['value'] = false;
                    } elseif (self::isComplex($argument['type'])) {
                        $argument['value'] = [];
                    }

                    $child = $this->elementFactory->getElement($type, $method, $argument['name']);
                    $child->label = $element->label.' > '.$child->label;

                    return ['element' => $child] + $argument;
                }, $objectResolved = ClassMethodResolver::resolve($type, $method), array_keys($objectResolved)));
                continue;
            }

            if (!$isEmpty) {
                $context[$key] = $element->normalize($value);
                continue;
            }

            if ($required || $given) {
                if (!$interactive) {
                    throw new \LogicException(sprintf('No value provided for "%s".', $field));
                }

                if ($element->generate($io, $generated)) {
                    $this->generatedValues[] = [$element->label, json_encode($generated)];
                    $context[$key] = $element->normalize($generated);
                } else {
                    $context[$key] = $this->askRequiredValue($io, $element, $value);
                }
                continue;
            }

            if ($this->flags & self::NO_DEFAULTS) {
                continue;
            }

            $context[$key] = $element->normalize($argument['default']);
        }

        if ($this->generatedValues) {
            $io->note('Generated values');
            $io->table([], $this->generatedValues);
            $this->generatedValues = [];
        }

        return $context;
    }

    private static function isComplex(?string $type): bool
    {
        return 'array' === $type || 'iterable' === $type || self::isObject($type);
    }

    private static function isObject(?string $type): bool
    {
        return null !== $type && (class_exists($type) || interface_exists($type, false));
    }

    private function resolve(): iterable
    {
        if (null !== $this->resolved) {
            return $this->resolved;
        }

        $this->resolved = [];

        foreach (ClassMethodResolver::resolve($this->class, $this->method) as $argument) {
            $this->resolved[] = [
                'element' => $this->elementFactory->getElement($this->class, $this->method, $argument['name']),
                'type' => isset($argument['type']) ? ($this->classMapping[$argument['type']] ?? $argument['type']) : null,
            ] + $argument;
        }

        return $this->resolved;
    }

    private function askRequiredValue(StyleInterface $io, ContextElement $element, $emptyValue)
    {
        if (null === $emptyValue) {
            return $element->askString($io);
        }

        if (false === $emptyValue) {
            return $element->askBool($io);
        }

        if ([] === $emptyValue) {
            return $element->askIterable($io);
        }

        return $emptyValue;
    }
}
