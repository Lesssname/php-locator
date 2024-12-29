<?php
declare(strict_types=1);

namespace LessLocator;

use LessLocator\Factory\Factory;
use Psr\Container\ContainerInterface;
use LessLocator\Exception\ServiceNotFound;

final class ServiceContainer implements ContainerInterface
{
    /** @var array<string, string> */
    private readonly array $aliases;
    /** @var array<string, class-string> */
    private readonly array $invokables;
    /** @var array<string, class-string<Factory>|Factory> */
    private readonly array $factories;

    /** @var array<string, object> */
    private array $initiated;

    /**
     * @param array{
     *     initiated?: array<string, object>,
     *     aliases?: array<string, string>,
     *     invokables?: array<string, class-string>,
     *     factories?: array<string, class-string<Factory>>,
     *  } $config
     */
    public function __construct(array $config)
    {
        $this->initiated = $config['initiated'] ?? [];
        $this->aliases = $config['aliases'] ?? [];
        $this->invokables = $config['invokables'] ?? [];
        $this->factories = $config['factories'] ?? [];
    }

    public function get(string $id): object
    {
        if (array_key_exists($id, $this->initiated)) {
            return $this->initiated[$id];
        }

        if (array_key_exists($id, $this->aliases)) {
            return $this->get($this->aliases[$id]);
        }

        if (array_key_exists($id, $this->invokables)) {
            $initiated = new $this->invokables[$id]();
        } elseif (array_key_exists($id, $this->factories)) {
            $factory = $this->factories[$id];

            if (is_string($factory)) {
                $factory = new $factory();
            }

            $initiated = $factory->create($this, $id);
        } else {
            throw new ServiceNotFound($id);
        }

        $this->initiated[$id] = $initiated;

        return $initiated;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->initiated)
            || array_key_exists($id, $this->aliases)
            || array_key_exists($id, $this->invokables)
            || array_key_exists($id, $this->factories);
    }
}
