<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Barcode Options
    |--------------------------------------------------------------------------
    |
    | Default configuration for barcode generation
    |
    */

    'defaults' => [
        'bar_width' => 2,
        'bar_height' => 60,
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'background_color' => '#FFFFFF',
        'foreground_color' => '#000000',
        'show_text' => true,
        'text_size' => 12,
        'text_margin' => 5,
    ],
];
