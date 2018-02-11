<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\DomainIdentityHelper;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EntityReferenceLoader
{
    private $em;
    private $classMapping;
    private $identityHelper;

    public function __construct(EntityManagerInterface $em, array $classMapping = [], DomainIdentityHelper $identityHelper = null)
    {
        $this->em = $em;
        $this->classMapping = $classMapping;
        $this->identityHelper = $identityHelper ?? new DomainIdentityHelper(new DomainIdentityMapping($em));
    }

    /**
     * @return null|object
     */
    public function __invoke(string $class, array $ids)
    {
        $class = $this->classMapping[$class] ?? $class;

        if ($this->em->getMetadataFactory()->isTransient($class)) {
            return null;
        }

        return $this->em->getPartialReference($class, $this->identityHelper->toIdentity($class, ...$ids));
    }
}
