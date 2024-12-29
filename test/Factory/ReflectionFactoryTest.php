<?php
declare(strict_types=1);

namespace LessLocatorTest\Factory;

use ArrayAccess;
use ArrayObject;
use ReflectionClass;
use ReflectionException;
use Psr\Container\ContainerInterface;
use LessLocator\Factory\ReflectionFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Container\NotFoundExceptionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Container\ContainerExceptionInterface;

#[CoversClass(ReflectionClass::class)]
class ReflectionFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCreateNoConstructor(): void
    {
        $class = new class {};

        $factory = new ReflectionFactory();
        $container = $this->createMock(ContainerInterface::class);

        $new = $factory->create($container, $class::class);

        self::assertInstanceOf($class::class, $new);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCreateNullableNotAvailable(): void
    {
        $class = new class (null) {
            public function __construct(public readonly ?ArrayAccess $arrayAccess)
            {}
        };

        $factory = new ReflectionFactory();
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('has')
            ->with(ArrayAccess::class)
            ->willReturn(false);
        $container
            ->expects(self::never())
            ->method('get');

        $new = $factory->create($container, $class::class);

        self::assertInstanceOf($class::class, $new);
        self::assertNull($new->arrayAccess);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCreateNullableAvailable(): void
    {
        $arrayObject = new ArrayObject();

        $class = new class (null) {
            public function __construct(public readonly ?ArrayAccess $arrayAccess)
            {}
        };

        $factory = new ReflectionFactory();
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('has')
            ->with(ArrayAccess::class)
            ->willReturn(true);
        $container
            ->expects(self::once())
            ->method('get')
            ->with(ArrayAccess::class)
            ->willReturn($arrayObject);

        $new = $factory->create($container, $class::class);

        self::assertInstanceOf($class::class, $new);
        self::assertSame($arrayObject, $new->arrayAccess);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testPassContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $class = new class ($container) {
            public function __construct(public readonly ContainerInterface $container)
            {}
        };

        $factory = new ReflectionFactory();

        $new = $factory->create($container, $class::class);

        self::assertInstanceOf($class::class, $new);
        self::assertSame($container, $new->container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCreate(): void
    {
        $var = new ArrayObject();

        $class = new class ($var) {
            public function __construct(public readonly ArrayObject $array)
            {}
        };

        $factory = new ReflectionFactory();
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::never())
            ->method('has');
        $container
            ->expects(self::once())
            ->method('get')
            ->with(ArrayObject::class)
            ->willReturn($var);

        $new = $factory->create($container, $class::class);

        self::assertInstanceOf($class::class, $new);
        self::assertSame($var, $new->array);
    }
}
