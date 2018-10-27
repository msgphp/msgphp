# Message Dispatcher

The domain message dispatcher is a utility trait. Its purpose is to dispatch a factorized message object using a
[object factory](../ddd/factory/object.md) and a [message bus](message-bus.md).

## API

### `dispatch(string $class, array $context = []): void`

Dispatches a message object factorized from `$class` and `$context`.

## Basic example

```php
<?php

use MsgPhp\Domain\Message\MessageDispatchingTrait;

class MyMessage
{
    public $argument;

    public function __construct(string $argument)
    {
        $this->argument = $argument;
    }
}

class MyClass
{
    use MessageDispatchingTrait;

    public function doSomething(): void
    {
        $this->dispatch(MyMessage::class, ['argument' => 'value']);
    }
}
```
