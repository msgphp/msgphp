<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\User\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserValueResolver implements ArgumentValueResolverInterface
{
    use TokenStorageAwareTrait;

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (User::class === ($type = $argument->getType()) || is_subclass_of($type, User::class)) {
            return $argument->isNullable() || $this->isUser();
        }

        return false;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        yield $this->toUser();
    }
}
