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
    use TokenStorageAwareTrait;

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        if (!$request->attributes->has($param = $configuration->getName())) {
            $request->attributes->set($param, $this->toUser());
        }

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        if (User::class == ($class = $configuration->getClass()) || is_subclass_of($class, User::class)) {
            $options = $configuration->getOptions();

            return !empty($options['current']) && ($configuration->isOptional() || $this->isUser());
        }

        return false;
    }
}
