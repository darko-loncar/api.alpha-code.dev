<?php
declare(strict_types=1);
namespace Crystal\Core;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;
use RuntimeException;

final class Container
{
    private array $services = [];

    public function register(callable $factory): void
    {
        $name = $this->getFactoryReturnType($factory);
        $this->services[$name] = $factory;
    }

    public function registerAsSingleton(callable $factory): void
    {
        $name = $this->getFactoryReturnType($factory);
        $instance = null;
        $resolved = false;

        $this->services[$name] = static function () use ($factory, &$instance, &$resolved): mixed
        {
            if (!$resolved) {
                $instance = $factory();
                $resolved = true;
            }

            return $instance;
        };
    }

    public function make(string $class): object
    {
        if (isset($this->services[$class])) {
            return ($this->services[$class])();
        }

        $ref = new ReflectionClass($class);

        if (!$ref->isInstantiable()) {
            throw new RuntimeException("Cannot instantiate '{$class}'. It may be an interface or abstract class.");
        }

        $constructor = $ref->getConstructor();
        if ($constructor === null) return new $class();

        $params = [];
        $constructorParams = $constructor->getParameters();
        foreach ($constructorParams as $param) {
            $type = $param->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $params[] = $param->getDefaultValue();
                    continue;
                }

                throw new RuntimeException(
                    "Cannot resolve parameter '{$param->getName()}' of '{$class}'."
                );
            }

            $params[] = $this->make($type->getName());
        }

        return $ref->newInstanceArgs($params);
    }

    private function getFactoryReturnType(callable $factory): string
    {
        $reflection = new ReflectionFunction($factory);
        $returnType = $reflection->getReturnType();
        if (!$returnType instanceof ReflectionNamedType || $returnType->isBuiltin()) {
            throw new InvalidArgumentException('Factory must have a class return type.');
            }

        return $returnType->getName();
    }
}
