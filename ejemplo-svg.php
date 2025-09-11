<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Barcode\Facades\Barcode;

echo "=== Ejemplos SVG sin dependencia GD ===\n\n";

// 1. Solo el patrón binario
echo "1. Patrón binario:\n";
$pattern = Barcode::generate('123456789');
echo 'Patrón: '.mb_substr($pattern, 0, 50)."...\n\n";

// 2. Generar SVG como string
echo "2. Generar SVG:\n";
$svg = Barcode::generateSvg('HELLO123');
echo 'SVG generado: '.mb_strlen($svg)." caracteres\n\n";

// 3. Guardar archivo SVG
echo "3. Guardar archivo SVG:\n";
$filename = __DIR__.'/codigo.svg';
Barcode::generateSvgFile('CODE128SVG', $filename, [
    'bar_width' => 3,
    'bar_height' => 80,
    'margin_left' => 20,
    'margin_right' => 20,
    'margin_top' => 10,
    'margin_bottom' => 30,
    'show_text' => true,
    'background_color' => '#FFFFFF',
    'foreground_color' => '#000000',
]);
echo "SVG guardado en: {$filename}\n\n";

// 4. SVG personalizado con colores
echo "4. SVG con colores personalizados:\n";
$coloredSvg = Barcode::generateSvg('COLORES', '', [
    'bar_width' => 2,
    'bar_height' => 60,
    'background_color' => '#F0F0F0',
    'foreground_color' => '#FF0000',
    'show_text' => true,
]);

$coloredFilename = __DIR__.'/codigo_rojo.svg';
file_put_contents($coloredFilename, $coloredSvg);
echo "SVG con barras rojas guardado en: {$coloredFilename}\n\n";

// 5. SVG base64 para web
echo "5. SVG base64 para uso en web:\n";
$svgBase64 = Barcode::generateSvgBase64('WEB789', [
    'bar_width' => 2,
    'bar_height' => 50,
    'show_text' => true,
]);
echo 'Para HTML: <img src="'.mb_substr($svgBase64, 0, 50)."...\" />\n\n";

// 6. SVG minimalista sin texto ni márgenes
echo "6. SVG minimalista:\n";
$minimalSvg = Barcode::generateSvg('MINIMAL', '', [
    'bar_width' => 1,
    'bar_height' => 40,
    'margin_left' => 0,
    'margin_right' => 0,
    'margin_top' => 0,
    'margin_bottom' => 0,
    'show_text' => false,
]);

file_put_contents(__DIR__.'/minimal.svg', $minimalSvg);
echo "SVG minimalista guardado\n\n";

echo "=== Ventajas del SVG ===\n";
echo "✅ Sin dependencia de GD\n";
echo "✅ Escalable sin pérdida de calidad\n";
echo "✅ Archivos pequeños\n";
echo "✅ Compatible con navegadores web\n";
echo "✅ Editable como texto\n";

echo "\n=== Ejemplo completado ===\n";
