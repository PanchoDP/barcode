<?php

declare(strict_types=1);

use Barcode\Facades\Barcode;
use PHPUnit\Framework\TestCase;

final class BarcodeFacadeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Barcode::clearResolvedInstances();
    }

    public function test_facade_can_generate_barcode()
    {
        $result = Barcode::generate('123456789');

        // Now returns the actual binary pattern, not a string message
        $this->assertIsString($result);
        $this->assertGreaterThan(100, mb_strlen($result)); // Code 128 patterns are long
        $this->assertMatchesRegularExpression('/^[01]+$/', $result); // Only 0s and 1s
    }

    public function test_facade_can_generate_barcode_with_options()
    {
        $result = Barcode::generateWithOptions('123456789', 3, 50);

        // Now returns SVG base64 data (no GD dependency)
        $this->assertIsString($result);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $result);
    }

    public function test_facade_can_validate_code()
    {
        $validResult = Barcode::validateCode('123456789');
        $invalidResult = Barcode::validateCode('');

        $this->assertTrue($validResult);
        $this->assertFalse($invalidResult);
    }

    public function test_facade_validates_long_codes()
    {
        $longCode = str_repeat('1', 49); // 49 characters (over limit)
        $validCode = str_repeat('1', 48); // 48 characters (at limit)

        $this->assertFalse(Barcode::validateCode($longCode));
        $this->assertTrue(Barcode::validateCode($validCode));
    }

    public function test_facade_uses_same_instance()
    {
        $instance1 = Barcode::getFacadeRoot();
        $instance2 = Barcode::getFacadeRoot();

        $this->assertSame($instance1, $instance2);
    }

    public function test_facade_can_clear_instances()
    {
        $instance1 = Barcode::getFacadeRoot();
        Barcode::clearResolvedInstances();
        $instance2 = Barcode::getFacadeRoot();

        $this->assertNotSame($instance1, $instance2);
    }
}
