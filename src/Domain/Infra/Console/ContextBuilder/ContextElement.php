<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\ContextBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ContextElement
{
    public $label;
    public $description;
    public $normalizer;
    public $generator;
    public $hidden;

    public function __construct(string $label, string $description = '', callable $normalizer = null, callable $generator = null, bool $hidden = false)
    {
        $this->label = $label;
        $this->description = $description;
        $this->normalizer = $normalizer;
        $this->generator = $generator;
        $this->hidden = $hidden;
    }
}
