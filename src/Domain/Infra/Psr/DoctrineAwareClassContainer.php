<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Psr;

use Doctrine\Common\Util\ClassUtils;
use Psr\Container\ContainerInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DoctrineAwareClassContainer implements ContainerInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function has($id)
    {
        return $this->container->has(ClassUtils::getRealClass($id));
    }

    public function get($id)
    {
        return $this->container->get(ClassUtils::getRealClass($id));
    }
}
