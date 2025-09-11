<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Barcode\Facades\Barcode;

echo "=== Ejemplo de uso del paquete Barcode Code 128 ===\n\n";

// 1. Generar un código de barras básico (patrón binario)
echo "1. Generar código básico (patrón binario):\n";
$basicBarcode = Barcode::generate('123456789');
echo 'Patrón: '.mb_substr($basicBarcode, 0, 50)."...\n";
echo 'Longitud: '.mb_strlen($basicBarcode)." bits\n\n";

// 2. Generar imagen SVG con opciones personalizadas (base64)
echo "2. Generar SVG con opciones personalizadas:\n";
$customBarcode = Barcode::generateWithOptions('ABC123DEF', 3, 80);
echo 'SVG base64: '.mb_substr($customBarcode, 0, 60)."...\n\n";

// 3. Generar archivo SVG
echo "3. Generar archivo SVG:\n";
$filename = __DIR__.'/ejemplo_barcode.svg';
Barcode::generateSvgFile('EJEMPLO123', $filename, [
    'bar_width' => 2,
    'bar_height' => 70,
    'margin_left' => 15,
    'margin_right' => 15,
    'show_text' => true,
]);
echo "Archivo SVG guardado: {$filename}\n\n";

// 4. Generar SVG base64 para uso en web
echo "4. Generar SVG base64 para uso en web:\n";
$webBarcode = Barcode::generateSvgBase64('WEB456', [
    'bar_width' => 2,
    'bar_height' => 60,
    'show_text' => true,
]);
echo 'Para usar en HTML: <img src="'.mb_substr($webBarcode, 0, 80)."...\" />\n\n";

// 5. Validar códigos
echo "5. Validar códigos Code 128:\n";
$codes = ['123456789', '', 'HELLO123', 'mixedCase', str_repeat('X', 50)];

foreach ($codes as $code) {
    $isValid = Barcode::validateCode($code);
    $status = $isValid ? '✅ Válido' : '❌ Inválido';
    echo "Código '{$code}': {$status}\n";
}

echo "\n=== Métodos disponibles ===\n";
echo "- generate(code): Genera patrón binario\n";
echo "- generateWithOptions(code, width, height): Genera SVG base64\n";
echo "- generateSvg(code, text, options): Genera SVG como string\n";
echo "- generateSvgFile(code, filename, options): Guarda archivo SVG\n";
echo "- generateSvgBase64(code, options): Genera SVG base64\n";
echo "- validateCode(code): Valida código Code 128\n";

echo "\n=== Ventajas del SVG ===\n";
echo "✅ Sin dependencias externas\n";
echo "✅ Escalable sin pérdida de calidad\n";
echo "✅ Compatible con todos los navegadores\n";
echo "✅ Archivos pequeños y editables\n";
echo "✅ Perfecto para web e impresión\n";

echo "\n=== Fin del ejemplo ===\n";
