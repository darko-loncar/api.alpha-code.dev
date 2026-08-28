<?php
declare(strict_types=1);
namespace Core;

use ReflectionClass;
use RuntimeException;

final class Container
{
    private array $services = [];

    public function register(
        callable $factory,
        string $name,
        bool $singleton = false
    ): void {
        $this->services[$name] = [
            'factory' => $factory,
            'singleton' => $singleton,
            'resolved' => false,
            'value' => null,
        ];
    }

    public function get(string $name): mixed
    {
        if (!array_key_exists($name, $this->services)) {
            return $this->make($name);
        }

        $service = &$this->services[$name];

        if (!$service['singleton']) {
            return ($service['factory'])();
        }

        if (!$service['resolved']) {
            $service['value'] = ($service['factory'])();
            $service['resolved'] = true;
        }

        return $service['value'];
    }


    public function make(string $class): object
    {
        $ref = new ReflectionClass($class);

        if (!$ref->isInstantiable()) {
            throw new RuntimeException(
                "Cannot instantiate '{$class}'. "
                . "It may be an interface or abstract class."
            );
        }


        $constructor = $ref->getConstructor();


        if (!$constructor) {
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
                    "Cannot resolve parameter '{$param->getName()}' "
                    . "of '{$class}'."
                );
            }

            $params[] = $this->get($type->getName());        }

        return $ref->newInstanceArgs($params);
    }

}

