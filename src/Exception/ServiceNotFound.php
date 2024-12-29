<?php
declare(strict_types=1);

namespace LessLocator\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * @psalm-immutable
 */
final class ServiceNotFound extends AbstractException implements NotFoundExceptionInterface
{
    public function __construct(private readonly string $id)
    {
        parent::__construct("Service '{$this->id}' not found");
    }
}
