<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Event;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Entity\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ResolveUserCredentialListener
{
    private $targetClass;

    public function __construct(string $targetClass)
    {
        $this->targetClass = $targetClass;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        if (User::class !== ($metadata = $event->getClassMetadata())->getName()) {
            return;
        }

        if (User::class === $this->targetClass || !is_subclass_of($this->targetClass, User::class)) {
            throw new \LogicException(sprintf('Class "%s" must be a sub class of "%s" to resolve the user credential.', $this->targetClass, User::class));
        }

        if (User::class === ($field = (new \ReflectionClass($this->targetClass))->getMethod('getCredential'))->getDeclaringClass()->getName()) {
            return;
        }

        if (null === ($type = $field->getReturnType()) || $type->isBuiltin() || $type->allowsNull()) {
            throw new \LogicException(sprintf('Method "%s::%s" must have a return type set to a non null-able credential class.', $this->targetClass, $field->getName()));
        }

        if (!is_subclass_of($class = $type->getName(), CredentialInterface::class)) {
            throw new \LogicException(sprintf('Return type "%s" of method "%s::%s" must be a sub class of "%s".', $class, $this->targetClass, $field->getName(), CredentialInterface::class));
        }

        $metadata->embeddedClasses['credential']['class'] = $class;
    }
}
