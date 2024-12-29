<?php
declare(strict_types=1);

namespace LessLocator\Factory;

use ReflectionClass;
use ReflectionMethod;
use ReflectionException;
use ReflectionParameter;
use ReflectionNamedType;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

final class ReflectionFactory implements Factory
{
    /**
     * @param ContainerInterface $container
     * @param class-string<T> $name
     *
     * @return object
     *
     * @template T of object
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function create(ContainerInterface $container, string $name): object
    {
        $reflection = new ReflectionClass($name);
        $constructor = $reflection->getConstructor();

        return new $name(...$this->getParameters($container, $constructor));
    }

    /**
     * @param ContainerInterface $container
     * @param ReflectionMethod|null $constructor
     *
     * @return iterable<object|null>
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private function getParameters(ContainerInterface $container, ?ReflectionMethod $constructor): iterable
    {
        if ($constructor === null) {
            return;
        }

        foreach ($constructor->getParameters() as $parameter) {
            yield $this->getParameterDependency($container, $parameter);
        }
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private function getParameterDependency(ContainerInterface $container, ReflectionParameter $parameter): ?object
    {
        $type = $parameter->getType();
        assert($type instanceof ReflectionNamedType);
        assert($type->isBuiltin() === false);

        if ($type->getName() === ContainerInterface::class) {
            return $container;
        }

        if ($parameter->allowsNull() && !$container->has($type->getName())) {
            return null;
        }

        $result = $container->get($type->getName());
        assert(is_object($result));

        return $result;
    }
}
