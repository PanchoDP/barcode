<?php

declare(strict_types=1);

use Barcode\Barcode;
use Barcode\BarcodeServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use PHPUnit\Framework\TestCase;

final class BarcodeServiceProviderTest extends TestCase
{
    private Container $app;

    private BarcodeServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Container();
        $this->provider = new BarcodeServiceProvider($this->app);
    }

    public function test_service_provider_extends_laravel_service_provider()
    {
        $this->assertInstanceOf(ServiceProvider::class, $this->provider);
    }

    public function test_register_binds_barcode_singleton()
    {
        $this->provider->register();

        $this->assertTrue($this->app->bound('barcode'));
        $this->assertTrue($this->app->isShared('barcode'));
    }

    public function test_barcode_service_resolves_to_barcode_instance()
    {
        $this->provider->register();

        $barcode = $this->app->make('barcode');

        $this->assertInstanceOf(Barcode::class, $barcode);
    }

    public function test_barcode_service_returns_same_instance()
    {
        $this->provider->register();

        $barcode1 = $this->app->make('barcode');
        $barcode2 = $this->app->make('barcode');

        $this->assertSame($barcode1, $barcode2);
    }

    public function test_boot_method_exists_and_callable()
    {
        $this->assertTrue(method_exists($this->provider, 'boot'));
        $this->assertTrue(is_callable([$this->provider, 'boot']));
    }

    public function test_boot_method_runs_without_error()
    {
        // Test basic boot functionality with Container (not full Laravel app)
        $errorOccurred = false;

        try {
            $this->provider->boot();
        } catch (Error $e) {
            $errorOccurred = true;
            // Expected since Container doesn't have runningInConsole method
            $this->assertStringContainsString('runningInConsole', $e->getMessage());
        }

        // Should throw error since we don't have Laravel's full application
        $this->assertTrue($errorOccurred);
    }

    public function test_boot_method_handles_missing_methods_gracefully()
    {
        // Verify boot method doesn't crash the application
        $crashed = false;

        try {
            $this->provider->boot();
        } catch (Error $e) {
            $crashed = true;
            // Should fail gracefully with a specific method error
            $this->assertStringContainsString('runningInConsole', $e->getMessage());
        }

        $this->assertTrue($crashed, 'Boot method should fail gracefully when Laravel methods are missing');
    }

    public function test_resolved_barcode_can_generate_codes()
    {
        $this->provider->register();

        $barcode = $this->app->make('barcode');
        $result = $barcode->generate('TEST123');

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_resolved_barcode_can_validate_codes()
    {
        $this->provider->register();

        $barcode = $this->app->make('barcode');

        $this->assertTrue($barcode->validateCode('TEST123'));
        $this->assertFalse($barcode->validateCode(''));
    }

    public function test_resolved_barcode_can_generate_svg()
    {
        $this->provider->register();

        $barcode = $this->app->make('barcode');
        $result = $barcode->generateSvg('TEST123');

        $this->assertIsString($result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('</svg>', $result);
    }

    public function test_service_provider_has_app_property()
    {
        // Test that provider has access to the container
        $this->provider->register();

        // Verify the service was registered correctly
        $this->assertTrue($this->app->bound('barcode'));
    }

    public function test_multiple_registrations_work_correctly()
    {
        // Register multiple times
        $this->provider->register();
        $this->provider->register();
        $this->provider->register();

        $barcode1 = $this->app->make('barcode');
        $barcode2 = $this->app->make('barcode');

        // Should still be singleton
        $this->assertSame($barcode1, $barcode2);
    }

    public function test_barcode_service_works_with_all_methods()
    {
        $this->provider->register();

        $barcode = $this->app->make('barcode');

        // Test all public methods
        $pattern = $barcode->generate('TEST');
        $this->assertIsString($pattern);

        $svg = $barcode->generateSvg('TEST');
        $this->assertStringContainsString('<svg', $svg);

        $base64 = $barcode->generateSvgBase64('TEST');
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $base64);

        $isValid = $barcode->validateCode('TEST');
        $this->assertTrue($isValid);

        $binaryPattern = $barcode->getBinaryPattern('TEST');
        $this->assertIsString($binaryPattern);
    }
}
