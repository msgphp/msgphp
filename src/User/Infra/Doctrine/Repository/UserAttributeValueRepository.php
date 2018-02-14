<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\Eav\{AttributeIdInterface, AttributeValueIdInterface};
use MsgPhp\User\Entity\UserAttributeValue;
use MsgPhp\User\Repository\UserAttributeValueRepositoryInterface;
use MsgPhp\User\UserIdInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserAttributeValueRepository implements UserAttributeValueRepositoryInterface
{
    use DomainEntityRepositoryTrait;

    private $alias = 'user_attribute_value';

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function findAllByAttributeId(AttributeIdInterface $attributeId, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        $qb = $this->createQueryBuilder();
        $this->addAttributeCriteria($qb, $attributeId);

        return $this->createResultSet($qb->getQuery(), $offset, $limit);
    }

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function findAllByAttributeIdAndValue(AttributeIdInterface $attributeId, $value, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        $qb = $this->createQueryBuilder();
        $this->addAttributeCriteria($qb, $attributeId, $value);

        return $this->createResultSet($qb->getQuery(), $offset, $limit);
    }

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function findAllByUserId(UserIdInterface $userId, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        return $this->doFindAllByFields(['user' => $userId], $offset, $limit);
    }

    /**
     * @return DomainCollectionInterface|UserAttributeValue[]
     */
    public function findAllByUserIdAndAttributeId(UserIdInterface $userId, AttributeIdInterface $attributeId, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        $qb = $this->createQueryBuilder();
        $this->addFieldCriteria($qb, ['user' => $userId]);
        $this->addAttributeCriteria($qb, $attributeId);

        return $this->createResultSet($qb->getQuery(), $offset, $limit);
    }

    public function find(UserIdInterface $userId, AttributeValueIdInterface $attributeValueId): UserAttributeValue
    {
        return $this->doFind(['user' => $userId, 'attributeValue' => $attributeValueId]);
    }

    public function exists(UserIdInterface $userId, AttributeValueIdInterface $attributeValueId): bool
    {
        return $this->doExists(['user' => $userId, 'attributeValue' => $attributeValueId]);
    }

    public function save(UserAttributeValue $userAttributeValue): void
    {
        $this->doSave($userAttributeValue);
    }

    public function delete(UserAttributeValue $userAttributeValue): void
    {
        $this->doDelete($userAttributeValue);
    }

    private function addAttributeCriteria(QueryBuilder $qb, AttributeIdInterface $attributeId, $value = null): void
    {
        if (3 === func_num_args()) {
            $param = $this->addFieldParameter($qb, 'attributeValue', md5(serialize($value)));

            $qb->join($this->alias.'.attributeValue', 'attribute_value', Join::WITH, 'attribute_value.checksum = '.$param);
        } else {
            $qb->join($this->alias.'.attributeValue', 'attribute_value');
        }

        $param = $this->addFieldParameter($qb, 'attribute', $attributeId);

        $qb->join('attribute_value.attribute', 'attribute', Join::WITH, 'attribute.id = '.$param);
    }
}
