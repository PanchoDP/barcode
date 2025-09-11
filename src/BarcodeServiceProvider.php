<?php

declare(strict_types=1);

namespace Barcode;

use Illuminate\Support\ServiceProvider;

class BarcodeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('barcode', function () {
            return new Barcode();
        });
    }

    public function boot(): void
    {
        // Configuration publishing if needed
        if ($this->app->runningInConsole() && function_exists('config_path')) {
            $this->publishes([
                __DIR__.'/../config/barcode.php' => config_path('barcode.php'),
            ], 'barcode-config');
        }
    }
}
