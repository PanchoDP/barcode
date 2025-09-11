<?php

declare(strict_types=1);

namespace Barcode\Facades;

use Barcode\Barcode;

class BarcodeFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Barcode::class;
    }
}
