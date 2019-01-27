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
    private $flags;
    private $elementFactory;
    private $resolved;
    private $fieldMapping = [];
    private $generatedValues = [];

    public static function getFieldName(string $argument, bool $isOption = true): string
    {
        $field = strtolower((string) preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1_\\2', '\\1_\\2'], $argument));

        return $isOption ? str_replace('_', '-', $field) : $field;
    }

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

        foreach ($this->resolve() as $argument => $metadata) {
            $isOption = true;
            if ('bool' === $metadata['type']) {
                $mode = InputOption::VALUE_NONE;
            } elseif (self::isComplex($metadata['type'])) {
                $mode = InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY;
            } elseif (!$metadata['required'] || ($this->flags & self::ALWAYS_OPTIONAL)) {
                $mode = InputOption::VALUE_OPTIONAL;
            } else {
                $mode = InputArgument::OPTIONAL;
                $isOption = false;
            }

            $field = self::getFieldName($argument, $isOption);
            if (!isset($origOptions[$field]) && !isset($origArgs[$field])) {
                $field = self::getUniqueFieldName($definition, $field, $isOption);
                /** @var ContextElement $element */
                $element = $metadata['element'];

                if ($isOption) {
                    $definition->addOption(new InputOption($field, null, $mode, $element->description));
                } else {
                    $definition->addArgument(new InputArgument($field, $mode, $element->description));
                }
            } else {
                $isOption = isset($origOptions[$field]);
            }

            $this->fieldMapping[$argument] = [$field, $isOption];
        }
    }

    public function getContext(InputInterface $input, StyleInterface $io, array $values = [], iterable $resolved = null): array
    {
        $context = $normalizers = [];
        $interactive = $input->isInteractive();

        foreach ($resolved ?? $this->resolve() as $argument => $metadata) {
            if (null === $resolved) {
                [$field, $isOption] = $this->fieldMapping[$argument];
                $value = $isOption ? $input->getOption($field) : $input->getArgument($field);
            } else {
                $field = $argument;
                $value = $metadata['value'] ?? null;
            }

            if (array_key_exists($argument, $values)) {
                $context[$argument] = $values[$argument];
                continue;
            }

            $isEmpty = null === $value || false === $value || [] === $value;
            $given = !$isEmpty || $input->hasParameterOption('--'.$field);
            $required = $metadata['required'] && !($this->flags & self::ALWAYS_OPTIONAL);
            $type = $metadata['type'];
            /** @var ContextElement $element */
            $element = $metadata['element'];

            if (\is_array($value) && self::isObject($type) && ($required || $given)) {
                $context[$argument] = $this->getContext($input, $io, [], $this->resolveNested($type, $value, $element));
                continue;
            }

            if (!$isEmpty) {
                $context[$argument] = $element->normalize($value);
                continue;
            }

            if ($required || $given) {
                if (!$interactive) {
                    throw new \LogicException(sprintf('No value provided for "%s".', $field));
                }

                if ($element->generate($io, $generated)) {
                    $this->generatedValues[] = [$element->label, json_encode($generated)];
                    $context[$argument] = $element->normalize($generated);
                } else {
                    $context[$argument] = self::askRequiredValue($io, $element, $value);
                }
                continue;
            }

            if ($this->flags & self::NO_DEFAULTS) {
                continue;
            }

            $context[$argument] = $element->normalize($metadata['default']);
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

    private static function askRequiredValue(StyleInterface $io, ContextElement $element, $emptyValue)
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

    private function resolve(): iterable
    {
        if (null !== $this->resolved) {
            return $this->resolved;
        }

        $this->resolved = [];

        foreach (ClassMethodResolver::resolve($this->class, $this->method) as $argument => $metadata) {
            $this->resolved[$argument] = [
                'element' => $this->elementFactory->getElement($this->class, $this->method, $argument),
                'type' => isset($metadata['type']) ? ($this->classMapping[$metadata['type']] ?? $metadata['type']) : null,
            ] + $metadata;
        }

        return $this->resolved;
    }

    private function resolveNested(string $type, array $parentValue, ContextElement $parentElement): iterable
    {
        $method = is_subclass_of($type, DomainCollectionInterface::class) || is_subclass_of($type, DomainIdInterface::class) ? 'fromValue' : '__construct';
        $resolved = [];

        foreach (ClassMethodResolver::resolve($type, $method) as $argument => $metadata) {
            if (array_key_exists($argument, $parentValue)) {
                $value = $parentValue[$argument];
            } elseif (array_key_exists($metadata['index'], $parentValue)) {
                $value = $parentValue[$metadata['index']];
            } elseif ('bool' === $metadata['type']) {
                $value = false;
            } elseif (self::isComplex($metadata['type'])) {
                $value = [];
            } else {
                $value = null;
            }

            $element = $this->elementFactory->getElement($type, $method, $argument);
            $element->label = $parentElement->label.' > '.$element->label;

            $resolved[$argument] = [
                'element' => $element,
                'type' => isset($metadata['type']) ? ($this->classMapping[$metadata['type']] ?? $metadata['type']) : null,
                'value' => $value,
            ] + $metadata;
        }

        return $resolved;
    }
}
