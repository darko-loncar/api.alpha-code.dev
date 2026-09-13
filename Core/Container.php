<?php
declare(strict_types=1);

namespace Core;

use ReflectionClass;
use RuntimeException;

final class Container
{
    private array $services = [];

    public function register(callable $factory, string $name): void
    {
        $this->services[$name] = $factory;
    }

    public function registerAsSingleton(callable $factory, string $name): void
    {
        $instance = null;
        $resolved = false;

        $this->services[$name] = function () use ($factory, &$instance, &$resolved): mixed {
            if (!$resolved) {
                $instance = $factory();
                $resolved = true;
            }

            return $instance;
        };
    }

    public function get(string $name): mixed
    {
        if (!isset($this->services[$name])) {
            return $this->make($name);
        }

        $service = $this->services[$name];

        return ($service)();
    }

    public function make(string $class): object
    {
        $ref = new ReflectionClass($class);

        if (!$ref->isInstantiable()) {
            throw new RuntimeException(
                "Cannot instantiate '{$class}'. It may be an interface or abstract class."
            );
        }

        $constructor = $ref->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $params = [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $params[] = $param->getDefaultValue();
                    continue;
                }

                throw new RuntimeException(
                    "Cannot resolve parameter '{$param->getName()}' of '{$class}'."
                );
            }

            $params[] = $this->get($type->getName());
        }

        return $ref->newInstanceArgs($params);
    }
}
