<?php

declare(strict_types=1);

namespace Barcode\Facades;

use InvalidArgumentException;
use RuntimeException;

abstract class Facade
{
    /** @var array<string, object> */
    protected static array $instances = [];

    /**
     * @param  array<mixed>  $args
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }

    abstract protected static function getFacadeAccessor(): string;

    final public static function getFacadeRoot(): ?object
    {
        $name = static::getFacadeAccessor();

        if (! isset(static::$instances[$name])) {
            $instance = static::resolveFacadeInstance($name);
            if ($instance !== null) {
                static::$instances[$name] = $instance;
            }
        }

        return static::$instances[$name] ?? null;
    }

    final public static function clearResolvedInstances(): void
    {
        static::$instances = [];
    }

    protected static function resolveFacadeInstance(mixed $name): ?object
    {
        if (is_object($name)) {
            return $name;
        }

        if (is_string($name)) {
            return new $name;
        }

        throw new InvalidArgumentException('Facade accessor must be a class name or object instance.');
    }
}
