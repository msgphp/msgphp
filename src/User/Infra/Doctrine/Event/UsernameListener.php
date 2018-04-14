<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Event;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\MappingException;
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
        $metadata = $event->getClassMetadata();

        if (!isset($this->mapping[$metadata->getName()])) {
            return;
        }

        try {
            $metadata->addEntityListener('prePersist', self::class, 'add');
            $metadata->addEntityListener('preUpdate', self::class, 'update');
            $metadata->addEntityListener('preRemove', self::class, 'remove');
        } catch (MappingException $e) {
            // duplicate
        }
    }

    /**
     * @param object $entity
     */
    public function add($entity, LifecycleEventArgs $event): void
    {
        $em = $event->getEntityManager();

        foreach ($this->getUsernames($entity, $em) as $username) {
            $em->persist($username);
        }
    }

    /**
     * @param object $entity
     */
    public function update($entity, PreUpdateEventArgs $event): void
    {
        $em = $event->getEntityManager();

        foreach ($this->getMapping($entity, $event->getEntityManager()) as $field => $mappedBy) {
            if (!$event->hasChangedField($field)) {
                continue;
            }

            if (null !== $username = $event->getOldValue($field)) {
                $this->updateUsernames[$username] = $event->getNewValue($field);
            } elseif (null !== $username = $event->getNewValue($field)) {
                $user = null === $mappedBy ? $entity : $em->getClassMetadata(get_class($entity))->getFieldValue($entity, $mappedBy);

                if (null === $user) {
                    continue;
                }

                $em->persist(new Username($user, $username));
            }
        }
    }

    /**
     * @param object $entity
     */
    public function remove($entity, LifecycleEventArgs $event): void
    {
        $em = $event->getEntityManager();
        $metadata = $em->getClassMetadata(get_class($entity));

        foreach (array_keys($this->getMapping($entity, $em)) as $field) {
            if (null === $username = $metadata->getFieldValue($entity, $field)) {
                continue;
            }

            $this->updateUsernames[$username] = null;
        }
    }

    public function postFlush(PostFlushEventArgs $event): void
    {
        if (!$this->updateUsernames) {
            return;
        }

        $em = $event->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select('u');
        $qb->from(Username::class, 'u');
        $qb->where($qb->expr()->in('u.username', ':usernames'));
        $qb->setParameter('usernames', array_keys($this->updateUsernames));

        foreach ($qb->getQuery()->getResult() as $username) {
            /* @var Username $username */
            $em->remove($username);

            if (isset($this->updateUsernames[$usernameValue = (string) $username])) {
                $em->persist(new Username($username->getUser(), $this->updateUsernames[$usernameValue]));
            }
        }

        $this->updateUsernames = [];

        $em->flush();
    }

    /**
     * @param object $entity
     *
     * @return Username[]
     */
    private function getUsernames($entity, EntityManagerInterface $em): iterable
    {
        $metadata = $em->getClassMetadata(get_class($entity));

        foreach ($this->getMapping($entity, $em) as $field => $mappedBy) {
            $user = null === $mappedBy ? $entity : $metadata->getFieldValue($entity, $mappedBy);

            if (null === $user || null === $username = $metadata->getFieldValue($entity, $field)) {
                continue;
            }

            yield new Username($user, $username);
        }
    }

    private function getMapping($entity, EntityManagerInterface $em): array
    {
        if (isset($this->mapping[$class = ClassUtils::getClass($entity)])) {
            return $this->mapping[$class];
        }

        foreach ($em->getClassMetadata($class)->parentClasses as $parent) {
            if (isset($this->mapping[$parent])) {
                return $this->mapping[$parent];
            }
        }

        throw new \LogicException(sprintf('No username mapping available for entity "%s".', $class));
    }
}
