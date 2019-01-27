<?php

declare(strict_types=1);

use MsgPhp\Domain\{DomainId, DomainIdInterface};

interface SomeIdInterface extends DomainIdInterface
{
}

class SomeId extends DomainId implements SomeIdInterface
{
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
