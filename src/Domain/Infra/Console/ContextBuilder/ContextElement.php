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

    public function __construct(string $label, string $description = '')
    {
        $this->label = $label;
        $this->description = $description;
    }
}
