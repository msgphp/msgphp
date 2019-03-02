<?php

declare(strict_types=1);

use MsgPhp\Domain\{DomainIdInterface, DomainIdTrait};

interface SomeIdInterface extends DomainIdInterface
{
}

class SomeId implements SomeIdInterface
{
    use DomainIdTrait;

    public function autocomplete(): void
    {
    }
}

$test = new class() {
    public function accept(SomeIdInterface $id): void
    {
    }
};

$test->accept(SomeId::fromValue('id'));
$test->accept($id = SomeId::fromValue('id'));

// test it autocompletes
//$id->auto
