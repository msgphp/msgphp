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
    private $factory;

    /** @var array<class-string, array<string, string>> */
    private $mapping;

    /** @var array<int, null|string> */
    private $removals = [];

    /** @var array<int, array{0:User,1:string}> */
    private $insertions = [];

    /**
     * @param array<class-string, array<string, string>> $mapping
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
            [$dirtyUser, $username] = $username;

            $user = $em->find(\get_class($dirtyUser), $dirtyUser->getId());
            $em->persist($this->factory->create(Username::class, compact('user', 'username')));
        }
    }

    public function prePersist(object $entity, LifecycleEventArgs $event): void
    {
        $em = $event->getEntityManager();

        foreach ($this->createUsernames($entity, $em) as $username) {
            $em->persist($username);
        }
    }

    public function preUpdate(object $entity, PreUpdateEventArgs $event): void
    {
        $em = $event->getEntityManager();

        foreach ($this->getMapping($entity, $em) as $field => $mappedBy) {
            if (!$event->hasChangedField($field)) {
                continue;
            }

            /** @var null|string $oldUsername */
            $oldUsername = $event->getOldValue($field);

            /** @var null|string $newUsername */
            $newUsername = $event->getNewValue($field);

            if (null !== $oldUsername) {
                $this->removals[] = $oldUsername;
            }

            if (null !== $newUsername) {
                /** @var null|User $user */
                $user = null === $mappedBy ? $entity : $em->getClassMetadata(\get_class($entity))->getFieldValue($entity, $mappedBy);

                if (null !== $user) {
                    $this->insertions[] = [$user, $newUsername];
                }
            }
        }
    }

    public function preRemove(object $entity, LifecycleEventArgs $event): void
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
     * @return iterable<int, Username>
     */
    private function createUsernames(object $entity, EntityManagerInterface $em): iterable
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
     * @return array<string, null|string>
     */
    private function getMapping(object $entity, EntityManagerInterface $em): array
    {
        if (isset($this->mapping[$class = \get_class($entity)])) {
            return $this->mapping[$class];
        }

        $metadata = $em->getClassMetadata($class);

        if (isset($this->mapping[$realClass = $metadata->getName()])) {
            /** @psalm-suppress PossiblyInvalidArrayOffset */
            return $this->mapping[$realClass];
        }

        foreach ($metadata->parentClasses as $parentClass) {
            if (isset($this->mapping[$parentClass])) {
                return $this->mapping[$parentClass];
            }
        }

        throw new \LogicException('No username mapping available for entity "'.$class.'".');
    }
}
