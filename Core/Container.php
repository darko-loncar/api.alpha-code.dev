<?php
declare(strict_types=1);
namespace Core;

use ReflectionClass;
use RuntimeException;
use Exception;

final class Container
{
    private array $services = [];

    public function registerService(string $name, mixed $service): void
    {
        $this->services[$name] = $service;
    }

    public function get(string $name): mixed
    {
        return $this->services[$name]
            ?? throw new RuntimeException("Service '{$name}' not found.");
    }

    public function make(string $class): object
    {
        $ref = new ReflectionClass($class);
        $constructor = $ref->getConstructor();

        if (!$constructor) {
            return new $class();
        }

        $params = [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if ($type && !$type->isBuiltin()) {
                $params[] = $this->get($type->getName());
            } else {
                throw new Exception("Cannot resolve {$param->getName()}");
            }
        }

        return $ref->newInstanceArgs($params);
    }

}

