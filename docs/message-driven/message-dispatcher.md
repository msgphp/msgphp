# Domain message dispatcher

The message dispatcher is a utility trait. Its purpose is to dispatch a message, factorized by a [object factory](../ddd/factory/object.md),
using any [message bus](domain-message-bus.md).

## API

> Exposed `private` as a trait. You can decide to [change method visibility](https://secure.php.net/manual/en/language.oop5.traits.php#language.oop5.traits.visibility)
on a per case basis.

### `dispatch(string $class, array $context = []): mixed`

Dispatches the given message class. The dispatcher can return a value coming from handlers, but is not required to do
so.

## Basic example

```php
<?php

use MsgPhp\Domain\Message\MessageDispatchingTrait;

class MyClass
{
    use MessageDispatchingTrait;

    public function doSomething(): void
    {
        $this->dispatch(SomeMessage::class, ['arg' => 'value']);
    }
}
```
