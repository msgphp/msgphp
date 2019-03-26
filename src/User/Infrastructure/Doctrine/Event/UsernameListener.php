<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Doctrine\Event;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\User;
use MsgPhp\User\Username;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UsernameListener
{
    /**
     * @var DomainObjectFactory
     */
    private $factory;

    /**
     * @var array[]
     */
    private $mapping;

    /**
     * @var string[]
     */
    private $removals = [];

    /**
     * @var array[]
     */
    private $insertions = [];

    /**
     * @param array[] $mapping
     */
    public function __construct(DomainObjectFactory $factory, array $mapping)
    {
        $this->factory = $factory;
        $this->mapping = $mapping;
    }

    public function preFlush(PreFlushEventArgs $event): void
    {
        $em = $event->getEntityManager();
        $usernameClass = $this->factory->getClass(Username::class);

        while (null !== $username = array_shift($this->removals)) {
            if (null !== $entity = $em->find($usernameClass, $username)) {
                $em->remove($entity);
            }
        }

        while (null !== $username = array_shift($this->insertions)) {
            /** @var User $dirtyUser */
            [$dirtyUser, $username] = $username;

            $user = $em->find(\get_class($dirtyUser), $dirtyUser->getId());
            $em->persist($this->factory->create(Username::class, compact('user', 'username')));
        }
    }

    /**
     * @param object $entity
     */
    public function prePersist($entity, LifecycleEventArgs $event): void
    {
        $em = $event->getEntityManager();

        foreach ($this->createUsernames($entity, $em) as $username) {
            $em->persist($username);
        }
    }

    /**
     * @param object $entity
     */
    public function preUpdate($entity, PreUpdateEventArgs $event): void
    {
        $em = $event->getEntityManager();

        foreach ($this->getMapping($entity, $em) as $field => $mappedBy) {
            if (!$event->hasChangedField($field)) {
                continue;
            }

            $oldUsername = $event->getOldValue($field);
            $newUsername = $event->getNewValue($field);

            if (null !== $oldUsername) {
                $this->removals[] = $oldUsername;
            }

            if (null !== $newUsername) {
                $user = null === $mappedBy ? $entity : $em->getClassMetadata(\get_class($entity))->getFieldValue($entity, $mappedBy);

                if (null !== $user) {
                    $this->insertions[] = [$user, $newUsername];
                }
            }
        }
    }

    /**
     * @param object $entity
     */
    public function preRemove($entity, LifecycleEventArgs $event): void
    {
        $em = $event->getEntityManager();
        $metadata = $em->getClassMetadata(\get_class($entity));

        foreach (array_keys($this->getMapping($entity, $em)) as $field) {
            if (null === $username = $metadata->getFieldValue($entity, $field)) {
                continue;
            }

            $em->remove($this->factory->reference(Username::class, compact('username')));
        }
    }

    /**
     * @param object $entity
     *
     * @return iterable|Username[]
     */
    private function createUsernames($entity, EntityManagerInterface $em): iterable
    {
        $metadata = $em->getClassMetadata(\get_class($entity));

        foreach ($this->getMapping($entity, $em) as $field => $mappedBy) {
            $user = null === $mappedBy ? $entity : $metadata->getFieldValue($entity, $mappedBy);

            if (null === $user || null === $username = $metadata->getFieldValue($entity, $field)) {
                continue;
            }

            yield $this->factory->create(Username::class, compact('user', 'username'));
        }
    }

    /**
     * @param object $entity
     */
    private function getMapping($entity, EntityManagerInterface $em): array
    {
        if (isset($this->mapping[$class = \get_class($entity)])) {
            return $this->mapping[$class];
        }

        $metadata = $em->getClassMetadata($class);

        if (isset($this->mapping[$realClass = $metadata->getName()])) {
            return $this->mapping[$realClass];
        }

        foreach ($metadata->parentClasses as $parentClass) {
            if (isset($this->mapping[$parentClass])) {
                return $this->mapping[$parentClass];
            }
        }

        throw new \LogicException(sprintf('No username mapping available for entity "%s".', $class));
    }
}
