<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Console\Definition;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Factory\ClassMethodResolver;
use MsgPhp\Domain\Infrastructure\Console\Context\ClassContextElementFactory;
use MsgPhp\Domain\Infrastructure\Console\Context\ContextElement;
use MsgPhp\Domain\Infrastructure\Console\Context\GenericClassContextElementFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ClassContextDefinition implements DomainContextDefinition
{
    public const ALWAYS_OPTIONAL = 1;
    public const NO_DEFAULTS = 2;
    public const REUSE_DEFINITION = 4;

    /** @var class-string */
    private $class;
    /** @var string */
    private $method;
    /** @var array<class-string, class-string> */
    private $classMapping;
    /** @var int */
    private $flags;
    /** @var ClassContextElementFactory */
    private $elementFactory;
    /** @var null|array<string, array> */
    private $resolved;
    /** @var array<string, array{0:string,1:bool}> */
    private $fieldMapping = [];
    /** @var array<int, array{0:string,1:string}> */
    private $generatedValues = [];

    /**
     * @param class-string                      $class
     * @param array<class-string, class-string> $classMapping
     */
    public function __construct(string $class, string $method, array $classMapping = [], int $flags = 0, ?ClassContextElementFactory $elementFactory = null)
    {
        $this->class = $class;
        $this->method = $method;
        $this->classMapping = $classMapping;
        $this->flags = $flags;
        $this->elementFactory = $elementFactory ?? new GenericClassContextElementFactory();
    }

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
                if ($isOption) {
                    $definition->addOption(new InputOption($field, null, $mode, $metadata['element']->description));
                } else {
                    $definition->addArgument(new InputArgument($field, $mode, $metadata['element']->description));
                }
            } else {
                $isOption = isset($origOptions[$field]);
            }

            $this->fieldMapping[$argument] = [$field, $isOption];
        }
    }

    /**
     * @param array<string, array{index:int,required:bool,default:mixed,type:string|class-string,element:ContextElement,value:mixed}>|null $resolved
     */
    public function getContext(InputInterface $input, StyleInterface $io, array $values = [], ?array $resolved = null): array
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

            if (\array_key_exists($argument, $values)) {
                $context[$argument] = $values[$argument];

                continue;
            }

            $isEmpty = null === $value || false === $value || [] === $value;
            $given = !$isEmpty || $input->hasParameterOption('--'.$field);
            $required = $metadata['required'] && !($this->flags & self::ALWAYS_OPTIONAL);
            $type = $metadata['type'];
            $element = $metadata['element'];

            if (\is_array($value) && self::isObject($type) && ($required || $given)) {
                /** @var class-string $type */
                $context[$argument] = $this->getContext($input, $io, [], $this->resolveNested($type, $value, $element));

                continue;
            }

            if (!$isEmpty) {
                $context[$argument] = $element->normalize($value);

                continue;
            }

            if ($required || $given) {
                if (!$interactive) {
                    throw new \LogicException('No value provided for "'.$field.'".');
                }

                if ($element->generate($io, $generated)) {
                    $context[$argument] = $element->normalize($generated);
                    $this->generatedValues[] = [$element->label, (string) json_encode($generated)]; // @todo use Dumper
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

    private static function isComplex(string $type): bool
    {
        return 'array' === $type || 'iterable' === $type || self::isObject($type);
    }

    private static function isObject(string $type): bool
    {
        return class_exists($type) || interface_exists($type, false);
    }

    /**
     * @param mixed $emptyValue
     *
     * @return mixed
     */
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

    /**
     * @return array<string, array{index:int,required:bool,default:mixed,type:string|class-string,element:ContextElement}>
     */
    private function resolve(): array
    {
        if (null === $this->resolved) {
            $this->resolved = [];

            foreach (ClassMethodResolver::resolve($this->class, $this->method) as $argument => $metadata) {
                $this->resolved[$argument] = [
                    'element' => $this->elementFactory->getElement($this->class, $this->method, $argument),
                    'type' => $this->classMapping[$metadata['type']] ?? $metadata['type'],
                ] + $metadata;
            }
        }

        return $this->resolved;
    }

    /**
     * @param class-string $class
     *
     * @return array<string, array{index:int,required:bool,default:mixed,type:string|class-string,element:ContextElement,value:mixed}>
     */
    private function resolveNested(string $class, array $parentValue, ContextElement $parentElement): array
    {
        $method = is_subclass_of($class, DomainId::class) ? 'fromValue' : '__construct';
        $resolved = [];

        foreach (ClassMethodResolver::resolve($class, $method) as $argument => $metadata) {
            if (\array_key_exists($argument, $parentValue)) {
                $value = $parentValue[$argument];
            } elseif (\array_key_exists($metadata['index'], $parentValue)) {
                $value = $parentValue[$metadata['index']];
            } elseif ('bool' === $metadata['type']) {
                $value = false;
            } elseif (self::isComplex($metadata['type'])) {
                $value = [];
            } else {
                $value = null;
            }

            $element = $this->elementFactory->getElement($class, $method, $argument);
            $element->label = $parentElement->label.' > '.$element->label;

            $resolved[$argument] = [
                'element' => $element,
                'type' => $this->classMapping[$metadata['type']] ?? $metadata['type'],
                'value' => $value,
            ] + $metadata;
        }

        return $resolved;
    }
}
