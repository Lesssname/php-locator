<?php
declare(strict_types=1);

namespace LessLocatorTest;

use ArrayObject;
use LessLocator\Factory\Factory;
use LessLocator\ServiceContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ServiceContainer::class)]
class ServiceContainerTest extends TestCase
{
    public function testHasNot(): void
    {
        $serviceContainer = new ServiceContainer([]);

        self::assertFalse($serviceContainer->has('foo'));
    }

    public function testFromInitiated(): void
    {
        $obj = new ArrayObject();

        $container = new ServiceContainer(
            [
                'initiated' => [
                    'foo' => $obj,
                ],
            ],
        );

        self::assertSame($obj, $container->get('foo'));
        self::assertTrue($container->has('foo'));
    }

    public function testViaAlias(): void
    {
        $obj = new ArrayObject();

        $container = new ServiceContainer(
            [
                'aliases' => [
                    'bar' => 'foo',
                ],
                'initiated' => [
                    'foo' => $obj,
                ],
            ],
        );

        self::assertSame($obj, $container->get('bar'));
        self::assertTrue($container->has('bar'));
    }

    public function testFromInvokable(): void
    {
        $container = new ServiceContainer(
            [
                'invokables' => [
                    'foo' => ArrayObject::class,
                ],
            ],
        );

        self::assertInstanceOf(ArrayObject::class, $container->get('foo'));
        self::assertTrue($container->has('foo'));
    }

    public function testFromFactoryClassString(): void
    {
        $factory = new class implements Factory {
            public function create(ContainerInterface $container, string $name): object
            {
                return new ArrayObject();
            }
        };

        $container = new ServiceContainer(
            [
                'factories' => [
                    'foo' => $factory::class,
                ],
            ],
        );

        self::assertInstanceOf(ArrayObject::class, $container->get('foo'));
        self::assertTrue($container->has('foo'));
    }

    public function testFromFactoryRemembers(): void
    {
        $arr = new ArrayObject();

        $factory = $this->createMock(Factory::class);

        $container = new ServiceContainer(
            [
                'factories' => [
                    'foo' => $factory,
                ],
            ],
        );

        $factory
            ->expects(self::once())
            ->method('create')
            ->with($container, 'foo')
            ->willReturn($arr);

        self::assertSame($arr, $container->get('foo'));
        self::assertSame($arr, $container->get('foo'));
        self::assertTrue($container->has('foo'));
    }
}
