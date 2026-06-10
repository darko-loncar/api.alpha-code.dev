<?php
declare(strict_types=1);
namespace Core;

final class Container
{
    private array $services = [];
    private array $instances = [];
    private static ?self $instance = null;

    public static function setInstance(self $container): void
    {
        self::$instance = $container;
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            throw new \RuntimeException("Container not initialized");
        }
        return self::$instance;
    }

    public function set(string $id, mixed $factory): void
    {
        $this->services[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->services[$id])) {
            throw new \InvalidArgumentException("Service not found: $id");
        }

        return $this->instances[$id] = ($this->services[$id]);
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->services[$id]);
    }
}