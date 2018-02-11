<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use MsgPhp\Domain\Factory\EntityReferenceLoader;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class EntityReferenceLoaderTest extends TestCase
{
    public function testInvoke(): void
    {
        $locator = $this->createMock(ContainerInterface::class);
        $locator->expects($this->any())
            ->method('has')
            ->willReturnCallback(function ($id): bool {
                return 'method_repo' === $id || 'callable_repo' === $id;
            });
        $locator->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($id) {
                if ('method_repo' === $id) {
                    return new class() {
                        public function find()
                        {
                            return ['method', func_get_args()];
                        }
                    };
                }

                if ('callable_repo' === $id) {
                    return function () {
                        return ['callable', func_get_args()];
                    };
                }

                throw new class() extends \Exception implements NotFoundExceptionInterface {
                };
            });
        $loader = new EntityReferenceLoader($locator, ['method_repo' => 'find']);

        $this->assertNull($loader('unknown', []));
        $this->assertSame(['method', [1]], $loader('method_repo', [1]));
        $this->assertSame(['callable', [1, '2']], $loader('callable_repo', [1, '2']));
    }

    public function testInvokeWithUnknownMethod(): void
    {
        $locator = $this->createMock(ContainerInterface::class);
        $locator->expects($this->any())
            ->method('has')
            ->willReturnCallback(function ($id): bool {
                return 'object_repo' === $id;
            });
        $locator->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($id) {
                if ('object_repo' === $id) {
                    return new \stdClass();
                }

                throw new class() extends \Exception implements NotFoundExceptionInterface {
                };
            });
        $loader = new EntityReferenceLoader($locator, ['object_repo' => 'unknown']);

        $this->expectException(\LogicException::class);

        $loader('object_repo', []);
    }

    public function testInvokeWithInvalidRepository(): void
    {
        $locator = $this->createMock(ContainerInterface::class);
        $locator->expects($this->any())
            ->method('has')
            ->willReturnCallback(function ($id): bool {
                return 'object_repo' === $id;
            });
        $locator->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($id) {
                if ('object_repo' === $id) {
                    return new \stdClass();
                }

                throw new class() extends \Exception implements NotFoundExceptionInterface {
                };
            });
        $loader = new EntityReferenceLoader($locator);

        $this->expectException(\LogicException::class);

        $loader('object_repo', []);
    }
}
