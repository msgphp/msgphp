<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection\Compiler;

use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManager;
use MsgPhp\Domain\Infra\DependencyInjection\ContainerHelper;
use MsgPhp\Eav\{Command, Repository};
use MsgPhp\EavBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class CleanupPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $doctrineRepositoryIds = [];
        foreach (glob(Configuration::getPackageDir().'/Infra/Doctrine/Repository/*Repository.php') as $file) {
            $doctrineRepositoryIds[] = 'MsgPhp\\User\\Infra\\Doctrine\\Repository\\'.basename($file, '.php');
        }
        ContainerHelper::removeIf($container, !$container->has(DoctrineEntityManager::class), $doctrineRepositoryIds);

        ContainerHelper::removeIf($container, !$container->has(Repository\AttributeRepositoryInterface::class), [
            Command\Handler\CreateAttributeHandler::class,
            Command\Handler\DeleteAttributeHandler::class,
        ]);
    }
}
