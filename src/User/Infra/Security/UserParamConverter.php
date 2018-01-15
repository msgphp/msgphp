<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\User\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserParamConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        // @todo implement; detect doctrine conversion, on fail pass here to let UserValueResolver pick it up
    }

    public function supports(ParamConverter $configuration): bool
    {
        if (null === $class = $configuration->getClass()) {
            return false;
        }

        return User::class === $class || is_subclass_of($class, User::class);
    }
}
