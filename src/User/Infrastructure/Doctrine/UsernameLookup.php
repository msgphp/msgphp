<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\User;
use MsgPhp\User\Username;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UsernameLookup
{
    /**
     * @var DomainObjectFactory
     */
    private $factory;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array[]
     */
    private $mapping;

    /**
     * @param array[] $mapping
     */
    public function __construct(DomainObjectFactory $factory, EntityManagerInterface $em, array $mapping)
    {
        $this->factory = $factory;
        $this->em = $em;
        $this->mapping = $mapping;
    }

    /**
     * @return iterable|Username[]
     */
    public function lookup(): iterable
    {
        foreach ($this->mapping as $class => $mapping) {
            $fields = [];
            foreach ($mapping as $field => $mappedBy) {
                $fields['e.'.$field] = true;

                if (null === $mappedBy) {
                    $fields['e.id'] = true;
                } else {
                    $fields['IDENTITY(e.'.$mappedBy.') AS '.$mappedBy] = true;
                }
            }

            $qb = $this->em->createQueryBuilder();
            $qb->select(array_keys($fields));
            $qb->from($class, 'e');

            foreach ($qb->getQuery()->getArrayResult() as $result) {
                foreach ($mapping as $field => $mappedBy) {
                    yield $this->factory->create(Username::class, [
                        'user' => $this->factory->reference(User::class, ['id' => $result[$mappedBy ?? 'id']]),
                        'username' => $result[$field],
                    ]);
                }
            }
        }
    }
}
