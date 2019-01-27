# Entity-Attribute-Value Bundle

A Symfony bundle for basic [EAV](https://en.wikipedia.org/wiki/Entity%E2%80%93attribute%E2%80%93value_model) management.

[![Latest Stable Version](https://poser.pugx.org/msgphp/eav-bundle/v/stable)](https://packagist.org/packages/msgphp/eav-bundle)

## Installation

```bash
composer require msgphp/eav-bundle
```

## Configuration

```php
<?php
// config/packages/msgphp_eav.php

use MsgPhp\Eav\Entity\{Attribute, AttributeValue};
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container) {
    $container->extension('msgphp_eav', [
        'class_mapping' => [
            Attribute::class => \App\Entity\Eav\Attribute::class,
            AttributeValue::class => \App\Entity\Eav\AttributeValue::class,
        ],
    ]);
};
```

## Documentation

- Read the [main documentation](https://msgphp.github.io/docs/)
- Try the Symfony [demo application](https://github.com/msgphp/symfony-demo-app)
- Get support on [Symfony's Slack `#msgphp` channel](https://symfony.com/slack-invite) or [raise an issue](https://github.com/msgphp/msgphp/issues/new)

## Contributing

This repository is **READ ONLY**. Issues and pull requests should be submitted in the
[main development repository](https://github.com/msgphp/msgphp).
