<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Security;

use MsgPhp\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserArgumentValueResolver implements ArgumentValueResolverInterface
{
    use TokenStorageAwareTrait;

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_a($argument->getType(), User::class, true) ? ($argument->isNullable() || $this->isUser()) : false;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        yield $this->toUser();
    }
}
