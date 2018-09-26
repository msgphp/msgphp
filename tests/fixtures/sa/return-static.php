<?php

use MsgPhp\Domain\{DomainId, DomainIdInterface, Infra\Doctrine\DomainCollection};

interface SomeIdInterface extends DomainIdInterface
{
}

class SomeId extends DomainId implements SomeIdInterface
{
    public function autocomplete()
    {
    }
}


$test = new class() {
    public function accept(SomeIdInterface $id)
    {
    }
};

$test->accept(SomeId::fromValue('id'));
$test->accept($id = SomeId::fromValue('id'));

// test it autocompletes
//$id->auto
