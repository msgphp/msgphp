<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use MsgPhp\User\Entity\Username;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UsernameListener
{
    private $mapping;
    private $updateUsernames = [];

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        if (!isset($this->mapping[($metadata = $event->getClassMetadata())->getName()])) {
            return;
        }

        $metadata->addEntityListener('prePersist', self::class, 'add');
        $metadata->addEntityListener('preUpdate', self::class, 'update');
        $metadata->addEntityListener('preRemove', self::class, 'remove');
    }

    /**
     * @param object $entity
     */
    public function add($entity, LifecycleEventArgs $event): void
    {
        $em = $event->getEntityManager();

        foreach ($this->getUsernames($entity, $event->getEntityManager()->getClassMetadata(get_class($entity))) as $username) {
            $em->persist($username);
        }
    }

    /**
     * @param object $entity
     */
    public function update($entity, PreUpdateEventArgs $event): void
    {
        if (!isset($this->mapping[$class = get_class($entity)])) {
            throw new \LogicException(sprintf('No username mapping available for entity "%s".', $class));
        }

        foreach ($this->mapping[$class] as $mapping) {
            $this->updateUsernames[$event->getOldValue($mapping['field'])] = $event->getNewValue($mapping['field']);
        }
    }

    /**
     * @param object $entity
     */
    public function remove($entity, LifecycleEventArgs $event): void
    {
        if (!isset($this->mapping[$class = get_class($entity)])) {
            throw new \LogicException(sprintf('No username mapping available for entity "%s".', $class));
        }

        $metadata = $event->getEntityManager()->getClassMetadata($class);

        foreach ($this->mapping[$class] as $mapping) {
            if (!isset($mapping['mapped_by'])) {
                continue;
            }

            $this->updateUsernames[$metadata->getFieldValue($entity, $mapping['field'])] = null;
        }
    }

    public function preFlush(PreFlushEventArgs $event): void
    {
        if (!$this->updateUsernames) {
            return;
        }

        $em = $event->getEntityManager();

        /** @var Username[] $usernames */
        $usernames = $em->getRepository(Username::class)->findBy(['username' => array_keys($this->updateUsernames)]);

        foreach ($usernames as $username) {
            $em->remove($username);

            if (isset($this->updateUsernames[$usernameValue = (string) $username])) {
                $em->persist(new Username($username->getUser(), $this->updateUsernames[$usernameValue]));
            }
        }

        $this->updateUsernames = [];
    }

    /**
     * @param object $entity
     *
     * @return Username[]
     */
    private function getUsernames($entity, ClassMetadataInfo $metadata): iterable
    {
        if (!isset($this->mapping[$class = get_class($entity)])) {
            throw new \LogicException(sprintf('No username mapping available for entity "%s".', $class));
        }

        foreach ($this->mapping[$class] as $mapping) {
            $user = isset($mapping['mapped_by']) ? $metadata->getFieldValue($entity, $mapping['mapped_by']) : $entity;

            yield new Username($user, $metadata->getFieldValue($entity, $mapping['field']));
        }
    }
}
