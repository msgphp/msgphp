# Repositories

A repository is not interface bound by default. Instead you can leverage various trait objects to rapidly create one, 
depending on the type of infrastructure needed. By design they follow the same API although there might be subtle
differences per implementation.

Note default (interface bound) repositories are provided per domain layer.

## Implementations

- `MsgPhp\Domain\Infra\InMemory\DomainEntityRepositoryTrait`
    - In-memory persistence
- `MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait`
    - Doctrine persistence
    - Requires `doctrine/orm`

## API

Note this API is exposed privately as a trait. You can decide to [change method visibility](https://secure.php.net/manual/en/language.oop5.traits.php#language.oop5.traits.visibility)
on a per case basis.

### `doFindAll(int $offset = 0, int $limit = 0): DomainCollectionInterface`

Finds all entities available.

### `doFindAllByFields(array $fields, int $offset = 0, int $limit = 0): DomainCollectionInterface`

Finds all entities matching all specified fields.

```php
<?php
$this->doFindAllByFields(['field' => null]);
$this->doFindAllByFields(['field' => 'value']);
$this->doFindAllByFields(['field' => ['one', 'of', 'these']);
```

### `doFind($id, ...$idN): object`

Finds a single entity by its identity.

### `doFindByFields(array $fields): object`

Finds the first entity matching all specified fields.

### `doExists($id, ...$idN): bool`

Verify if an entity exists by its identity.

### `doExistsByFields(array $fields): bool`

Verify if an entity exists matching all specified fields.

### `doSave(object $entity): void`

Persist an entity in the identity map. The entity will be available on any subsequent query.

### `doDelete(object $entity): void`

Remove an entity from the identity map. The entity will be unavailable on any subsequent query.

## Generic Doctrine repository example

```php
<?php

use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use Doctrine\ORM\EntityManagerInterface;

class MyGenericRepository
{
    use DomainEntityRepositoryTrait {
        doFind as public find;
        doExists as public exists;
    }
}

/** @var EntityManagerInterface $em */
$em = ...;

$repository = new MyGenericRepository(MyEntity::class, $em); 

/** @var MyEntity $entity */
$entity = $repository->find('1');
```
