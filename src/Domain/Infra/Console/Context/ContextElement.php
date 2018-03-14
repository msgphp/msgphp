<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\Context;

use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ContextElement
{
    public $label;
    public $description;
    private $hide = false;
    private $generator;
    private $normalizer;

    public function __construct(string $label, string $description = '')
    {
        $this->label = $label;
        $this->description = $description;
    }

    public function hide(bool $flag = true): self
    {
        $this->hide = $flag;

        return $this;
    }

    public function generator(callable $generator): self
    {
        $this->generator = $generator;

        return $this;
    }

    public function normalizer(callable $normalizer): self
    {
        $this->normalizer = $normalizer;

        return $this;
    }

    public function normalize($value)
    {
        return null === $this->normalizer ? $value : ($this->normalizer)($value);
    }

    public function generate(StyleInterface $io, &$generated): bool
    {
        $generated = null;
        $result = false;

        if (null !== $this->generator && $result = $io->confirm(sprintf('Generate a value for "%s"?', $this->label))) {
            $generated = ($this->generator)();
        }

        unset($generated);

        return $result;
    }

    public function askString(StyleInterface $io): string
    {
        do {
            $value = $this->hide ? $io->askHidden($this->label) : $io->ask($this->label);
        } while (null === $value);

        return $this->normalize($value);
    }

    public function askBool(StyleInterface $io): bool
    {
        return $this->normalize($io->confirm($this->label, false));
    }

    public function askIterable(StyleInterface $io): bool
    {
        $i = 0;
        $value = [];

        do {
            $label = $this->label.' ['.$i.']';
            $value[] = $this->hide ? $io->askHidden($label) : $io->ask($label);
            ++$i;
        } while ($io->confirm('Add another value?', false));

        return $this->normalize($value);
    }
}
