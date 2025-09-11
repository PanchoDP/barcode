<?php

declare(strict_types=1);

use Barcode\Barcode;
use Barcode\Facades\BarcodeFacade;
use Barcode\Facades\Facade;
use PHPUnit\Framework\TestCase;

final class FacadeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TestFacade::clearResolvedInstances();
    }

    protected function tearDown(): void
    {
        TestFacade::clearResolvedInstances();
        parent::tearDown();
    }

    public function test_facade_resolves_instance_from_class_name()
    {
        $instance = TestFacade::getFacadeRoot();

        $this->assertInstanceOf(TestClass::class, $instance);
    }

    public function test_facade_returns_same_instance_on_multiple_calls()
    {
        $instance1 = TestFacade::getFacadeRoot();
        $instance2 = TestFacade::getFacadeRoot();

        $this->assertSame($instance1, $instance2);
    }

    public function test_facade_clear_resolved_instances()
    {
        $instance1 = TestFacade::getFacadeRoot();
        TestFacade::clearResolvedInstances();
        $instance2 = TestFacade::getFacadeRoot();

        $this->assertNotSame($instance1, $instance2);
        $this->assertInstanceOf(TestClass::class, $instance2);
    }

    public function test_facade_call_static_forwards_to_instance()
    {
        $result = TestFacade::testMethod('hello', 'world');

        $this->assertEquals('hello world', $result);
    }

    public function test_facade_call_static_throws_exception_when_no_root()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A facade root has not been set.');

        EmptyFacade::someMethod();
    }

    public function test_resolve_facade_instance_with_object()
    {
        $object = new TestClass();
        $result = TestFacade::resolveFacadeInstancePublic($object);

        $this->assertSame($object, $result);
    }

    public function test_resolve_facade_instance_with_invalid_type()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Facade accessor must be a class name or object instance.');

        TestFacade::resolveFacadeInstancePublic(123);
    }

    public function test_barcode_facade_extends_facade()
    {
        $this->assertInstanceOf(Facade::class, new BarcodeFacade());
    }

    public function test_barcode_facade_returns_correct_accessor()
    {
        $accessor = BarcodeFacadeTestExtension::getFacadeAccessorPublic();

        $this->assertEquals(Barcode::class, $accessor);
    }

    public function test_facade_handles_non_existent_class()
    {
        $this->expectException(Error::class);

        NonExistentClassFacade::getFacadeRoot();
    }

    public function test_facade_with_null_return_from_resolve()
    {
        $result = NullResolveFacade::getFacadeRoot();

        $this->assertNull($result);
    }

    public function test_facade_handles_method_with_many_arguments()
    {
        $result = TestFacade::testMethodManyArgs(1, 2, 3, 4, 5);

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function test_facade_preserves_return_types()
    {
        $this->assertIsString(TestFacade::getString());
        $this->assertIsInt(TestFacade::getInt());
        $this->assertIsArray(TestFacade::getArray());
        $this->assertIsBool(TestFacade::getBool());
        $this->assertNull(TestFacade::getNull());
    }
}

// Test helper classes
class TestClass
{
    public function testMethod(string $arg1, string $arg2): string
    {
        return $arg1.' '.$arg2;
    }

    public function testMethodManyArgs(int $a, int $b, int $c, int $d, int $e): array
    {
        return [$a, $b, $c, $d, $e];
    }

    public function getString(): string
    {
        return 'test';
    }

    public function getInt(): int
    {
        return 42;
    }

    public function getArray(): array
    {
        return [1, 2, 3];
    }

    public function getBool(): bool
    {
        return true;
    }

    public function getNull(): ?string
    {
        return null;
    }
}

class TestFacade extends Facade
{
    // Public wrapper for testing protected method
    public static function resolveFacadeInstancePublic(mixed $name): ?object
    {
        return static::resolveFacadeInstance($name);
    }

    protected static function getFacadeAccessor(): string
    {
        return TestClass::class;
    }
}

class EmptyFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return '';
    }

    protected static function resolveFacadeInstance(mixed $name): ?object
    {
        return null;
    }
}

class NonExistentClassFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'NonExistentClass';
    }
}

class NullResolveFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'test';
    }

    protected static function resolveFacadeInstance(mixed $name): ?object
    {
        return null;
    }
}

class BarcodeFacadeTestExtension extends BarcodeFacade
{
    public static function getFacadeAccessorPublic(): string
    {
        return static::getFacadeAccessor();
    }
}
