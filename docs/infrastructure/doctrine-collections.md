# Doctrine Collections

An overview of available infrastructural code when using Doctrine's [Collections][collections-project].

- Requires [doctrine/collections]

## Domain Collection

A Doctrine tailored [domain collection](../ddd/collections.md) is provided by `MsgPhp\Domain\Infra\Doctrine\DomainCollection`.

### Basic Example

```php
<?php

use Doctrine\Common\Collections\ArrayCollection;
use MsgPhp\Domain\Infra\Doctrine\DomainCollection;

// --- SETUP ---

$collection = new DomainCollection(new ArrayCollection([1, 2, 3]));
```

[collections-project]: http://www.doctrine-project.org/projects/collections.html
[doctrine/collections]: https://packagist.org/packages/doctrine/collections
