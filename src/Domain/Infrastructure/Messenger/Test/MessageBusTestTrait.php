<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Messenger\Test;

use MsgPhp\Domain\Infrastructure\Messenger\DomainMessageBus;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
trait MessageBusTestTrait
{
    /**
     * @var MessageBusInterface
     */
    private static $bus;

    /**
     * @var object[][]
     */
    private static $dispatchedMessages = [];

    abstract protected static function getMessageHandlers(): iterable;

    private static function initBus(): void
    {
        self::$bus = new MessageBus([new HandleMessageMiddleware(new HandlersLocator(['*' => [function ($message): void {
            self::$dispatchedMessages[$messageClass = \get_class($message)][] = $message;

            foreach (self::getMessageHandlers() as $class => $handler) {
                if ($class === $messageClass) {
                    $handler($message);
                }
            }
        }]]))]);
    }

    private static function destroyBus(): void
    {
        self::$bus = null;
    }

    private static function cleanBus(): void
    {
        self::$dispatchedMessages = [];
    }

    private static function createDomainMessageBus(): DomainMessageBus
    {
        return new DomainMessageBus(self::$bus, self::$bus);
    }

    private static function assertMessageIsDispatched(string $class, callable $assertion = null): void
    {
        if (!isset(self::$dispatchedMessages[$class])) {
            throw new \LogicException(sprintf('Message "%s" is not dispatched.', $class));
        }

        if (null === $assertion) {
            return;
        }

        foreach (self::$dispatchedMessages[$class] as $i => $message) {
            $assertion($message, $i);
        }
    }

    private static function assertMessageIsDispatchedOnce(string $class, callable $assertion = null): void
    {
        if (1 !== \count(self::$dispatchedMessages[$class] ?? [])) {
            throw new \LogicException(sprintf('Message "%s" is not dispatched exactly once.', $class));
        }

        self::assertMessageIsDispatched($class, $assertion);
    }

    private static function assertMessageIsNotDispatched(string $class): void
    {
        if (isset(self::$dispatchedMessages[$class])) {
            throw new \LogicException(sprintf('Message "%s" is dispatched.', $class));
        }
    }
}
