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
        // @todo implement
    }

    public function supports(ParamConverter $configuration): bool
    {
        if (null === ($class = $configuration->getClass()) || !($options = $configuration->getOptions())) {
            return false;
        }

        if (User::class !== $class || !is_subclass_of($class, User::class)) {
            return false;
        }

        return (bool) $options['current'] ?? false;
    }
}
