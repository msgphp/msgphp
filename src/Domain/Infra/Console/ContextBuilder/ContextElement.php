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
    public $hidden;
    public $generator;

    public function __construct(string $label, string $description = '', bool $hidden = false, callable $generator = null)
    {
        $this->label = $label;
        $this->description = $description;
        $this->hidden = $hidden;
        $this->generator = $generator;
    }
}
