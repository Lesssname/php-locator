<?php
declare(strict_types=1);

namespace LessLocator\Factory;

use Psr\Container\ContainerInterface;

interface Factory
{
    public function create(ContainerInterface $container, string $name): object;
}
