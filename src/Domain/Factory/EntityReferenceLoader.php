<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use Psr\Container\ContainerInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EntityReferenceLoader
{
    private $repositoryLocator;
    private $methodMap;

    public function __construct(ContainerInterface $repositoryLocator, array $methodMap = [])
    {
        $this->repositoryLocator = $repositoryLocator;
        $this->methodMap = $methodMap;
    }

    /**
     * @return null|object
     */
    public function __invoke(string $class, array $ids)
    {
        if (!$this->repositoryLocator->has($class)) {
            return null;
        }

        $locator = $this->repositoryLocator->get($class);

        if (isset($this->methodMap[$class])) {
            if (!method_exists($locator, $method = $this->methodMap[$class])) {
                throw new \LogicException(sprintf('Cannot load entity from "%s" as method "%s" does not exists.', get_class($locator), $method));
            }

            return $locator->$method(...$ids);
        }

        if (!is_callable($locator)) {
            throw new \LogicException(sprintf('Cannot load entity from "%s" as it\'s not callable nor has a known callable method.', get_class($locator)));
        }

        return $locator(...$ids);
    }
}
