<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\ORM\Tools\SchemaTool;
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\Domain\Tests\AbstractDomainEntityRepositoryTraitTest;
use MsgPhp\Domain\Tests\Fixtures\DomainEntityRepositoryTraitInterface;
use MsgPhp\Domain\Tests\Fixtures\Entities;

final class DomainEntityRepositoryTraitTest extends AbstractDomainEntityRepositoryTraitTest
{
    use EntityManagerTrait;

    protected function setUp(): void
    {
        if (!self::$em->isOpen()) {
            self::$em = self::$em::create(self::$em->getConnection(), self::$em->getConfiguration(), self::$em->getEventManager());
        }

        (new SchemaTool(self::$em))->createSchema(self::$em->getMetadataFactory()->getAllMetadata());
    }

    protected function tearDown(): void
    {
        (new SchemaTool(self::$em))->dropDatabase();

        self::$em->clear();
    }

    public function testDuplicateFieldParameters(): void
    {
        $repository = new class(Entities\TestEntity::class, self::$em) {
            use DomainEntityRepositoryTrait {
                createQueryBuilder as public;
                addFieldCriteria as public;
            }

            private $alias = 'root';
        };
        $qb = $repository->createQueryBuilder();
        $repository->addFieldCriteria($qb, ['foo.bar' => 'bar1']);
        $repository->addFieldCriteria($qb, ['foo.bar' => 'bar2']);
        $repository->addFieldCriteria($qb, ['foo.bar' => 'bar3']);

        $this->assertCount(3, $qb->getParameters());
    }

    protected function equalsEntity($expected, $actual)
    {
        $equals = true;
        foreach (($r = (new \ReflectionObject($expected)))->getProperties() as $property) {
            $property->setAccessible(true);
            $expectedValue = $property->getValue($expected);
            $actualValue = $property->getValue($actual);

            if (is_object($expectedValue) && is_object($actualValue)) {
                if (!$this->equalsEntity($expectedValue, $actualValue)) {
                    $equals = false;
                    break;
                }

                continue;
            }

            if ($expectedValue !== $actualValue) {
                $equals = false;
                break;
            }
        }

        return $equals;
    }

    protected static function createRepository(string $class): DomainEntityRepositoryTraitInterface
    {
        return new class($class, self::$em) implements DomainEntityRepositoryTraitInterface {
            use DomainEntityRepositoryTrait {
                doFindAll as public;
                doFindAllByFields as public;
                doFind as public;
                doFindByFields as public;
                doExists as public;
                doExistsByFields as public;
                doSave as public;
                doDelete as public;
            }

            private $alias = 'root';
        };
    }

    protected static function flushEntities(iterable $entities): void
    {
        foreach ($entities as $entity) {
            self::$em->persist($entity);
        }

        self::$em->flush();
    }
}
