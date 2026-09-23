<?php
declare(strict_types=1);

namespace Crystal\Tests;

use Crystal\Core\Container;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ContainerTest extends TestCase
{
    public function testItCreatesClassWithoutConstructor(): void
    {
        $container = new Container();

        $result = $container->make(SimpleClass::class);

        $this->assertInstanceOf(SimpleClass::class, $result);
    }

    public function testItResolvesConstructorDependencies(): void
    {
        $container = new Container();

        $result = $container->make(Service::class);

        $this->assertInstanceOf(Service::class, $result);
        $this->assertInstanceOf(Repository::class, $result->repository);
    }

    public function testItResolvesNestedDependencies(): void
    {
        $container = new Container();

        $result = $container->make(Controller::class);

        $this->assertInstanceOf(Controller::class, $result);
        $this->assertInstanceOf(Service::class, $result->service);
        $this->assertInstanceOf(Repository::class, $result->service->repository);
    }

    public function testItUsesDefaultConstructorValue(): void
    {
        $container = new Container();

        $result = $container->make(ConfiguredClass::class);

        $this->assertSame('default', $result->value);
    }

    public function testItUsesRegisteredFactory(): void
    {
        $container = new Container();

        $container->register(
            fn (): Repository => new Repository()
        );

        $result = $container->make(Service::class);

        $this->assertInstanceOf(Service::class, $result);
        $this->assertInstanceOf(Repository::class, $result->repository);
    }

    public function testRegisteredFactoryIsCalledEveryTime(): void
    {
        $container = new Container();

        $container->register(
            fn (): Repository => new Repository()
        );

        $first = $container->make(Repository::class);
        $second = $container->make(Repository::class);

        $this->assertNotSame($first, $second);
    }

    public function testRegisteredSingletonReturnsSameInstance(): void
    {
        $container = new Container();

        $container->registerAsSingleton(
            fn (): Repository => new Repository()
        );

        $first = $container->make(Repository::class);
        $second = $container->make(Repository::class);

        $this->assertSame($first, $second);
    }

    public function testSingletonFactoryIsCalledOnlyOnce(): void
    {
        $container = new Container();

        $calls = 0;

        $container->registerAsSingleton(
            function () use (&$calls): Repository {
                $calls++;

                return new Repository();
            }
        );

        $container->make(Repository::class);
        $container->make(Repository::class);
        $container->make(Repository::class);

        $this->assertSame(1, $calls);
    }

    public function testItRejectsUninstantiableClass(): void
    {
        $container = new Container();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Cannot instantiate '" . AbstractService::class . "'."
        );

        $container->make(AbstractService::class);
    }

    public function testItRejectsUnresolvableParameter(): void
    {
        $container = new Container();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Cannot resolve parameter 'value' of '" . RequiresValue::class . "'."
        );

        $container->make(RequiresValue::class);
    }

    public function testItRejectsFactoryWithoutClassReturnType(): void
    {
        $container = new Container();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Factory must have a class return type.'
        );

        $container->register(
            fn () => new Repository()
        );
    }

    public function testItRejectsFactoryWithBuiltinReturnType(): void
    {
        $container = new Container();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Factory must have a class return type.'
        );

        $container->register(
            fn (): string => 'test'
        );
    }
}


/*
 * Test fixtures
 */

final class SimpleClass
{
}

final class Repository
{
}

final class Service
{
    public function __construct(
        public Repository $repository
    ) {
    }
}

final class Controller
{
    public function __construct(
        public Service $service
    ) {
    }
}

final class ConfiguredClass
{
    public function __construct(
        public string $value = 'default'
    ) {
    }
}

abstract class AbstractService
{
}

final class RequiresValue
{
    public function __construct(
        string $value
    ) {
    }
}
