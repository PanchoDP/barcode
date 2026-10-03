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
}
