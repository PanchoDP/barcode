<?php

declare(strict_types=1);

use Barcode\Barcode;
use PHPUnit\Framework\TestCase;

final class BarcodeTest extends TestCase
{
    private Barcode $barcode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->barcode = new Barcode();
    }

    public function test_constructor_initializes_generator()
    {
        $barcode = new Barcode();

        $this->assertInstanceOf(Barcode::class, $barcode);
    }

    public function test_generate_returns_binary_pattern()
    {
        $result = $this->barcode->generate('TEST123');

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
        $this->assertGreaterThan(100, mb_strlen($result));
    }

    public function test_generate_with_options_ignores_options()
    {
        $result = $this->barcode->generate('TEST123', ['unused' => 'option']);

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
    }

    public function test_generate_with_options_creates_base64_svg()
    {
        $result = $this->barcode->generateWithOptions('TEST123', 3, 80);

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $result);

        $base64 = mb_substr($result, 26);
        $decoded = base64_decode($base64);

        $this->assertStringContainsString('<svg', $decoded);
        $this->assertStringContainsString('TEST123', $decoded);
    }

    public function test_generate_with_options_default_parameters()
    {
        $result = $this->barcode->generateWithOptions('TEST123');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $result);
    }

    public function test_validate_code_returns_true_for_valid_code()
    {
        $this->assertTrue($this->barcode->validateCode('TEST123'));
        $this->assertTrue($this->barcode->validateCode('123456'));
        $this->assertTrue($this->barcode->validateCode('!@#$%'));
    }

    public function test_validate_code_returns_false_for_invalid_code()
    {
        $this->assertFalse($this->barcode->validateCode(''));
        $this->assertFalse($this->barcode->validateCode(str_repeat('A', 49)));
    }

    public function test_get_binary_pattern()
    {
        $pattern = $this->barcode->getBinaryPattern('TEST123');

        $this->assertIsString($pattern);
        $this->assertMatchesRegularExpression('/^[01]+$/', $pattern);
    }

    public function test_get_binary_pattern_same_as_generate()
    {
        $code = 'TEST123';
        $pattern1 = $this->barcode->getBinaryPattern($code);
        $pattern2 = $this->barcode->generate($code);

        $this->assertEquals($pattern1, $pattern2);
    }

    public function test_generate_svg_with_default_options()
    {
        $svg = $this->barcode->generateSvg('TEST123');

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringEndsWith('</svg>', $svg);
        $this->assertStringContainsString('TEST123', $svg);
    }

    public function test_generate_svg_with_custom_text()
    {
        $svg = $this->barcode->generateSvg('123456', 'Custom Text');

        $this->assertStringContainsString('Custom Text', $svg);
        $this->assertStringNotContainsString('123456', $svg);
    }

    public function test_generate_svg_with_empty_text_uses_code()
    {
        $svg = $this->barcode->generateSvg('TEST123', '');

        $this->assertStringContainsString('TEST123', $svg);
    }

    public function test_generate_svg_with_options()
    {
        $options = [
            'bar_width' => 3,
            'bar_height' => 100,
            'background_color' => '#FF0000',
            'foreground_color' => '#0000FF',
        ];

        $svg = $this->barcode->generateSvg('TEST123', 'CUSTOM', $options);

        $this->assertStringContainsString('fill="#FF0000"', $svg);
        $this->assertStringContainsString('fill="#0000FF"', $svg);
        $this->assertStringContainsString('CUSTOM', $svg);
    }

    public function test_generate_svg_file()
    {
        $filename = sys_get_temp_dir().'/barcode_test.svg';
        $options = ['bar_width' => 2];

        $result = $this->barcode->generateSvgFile('TEST123', $filename, $options);

        $this->assertEquals($filename, $result);
        $this->assertFileExists($filename);

        $content = file_get_contents($filename);
        $this->assertStringContainsString('<svg', $content);
        $this->assertStringContainsString('TEST123', $content);

        unlink($filename);
    }

    public function test_generate_svg_file_with_empty_options()
    {
        $filename = sys_get_temp_dir().'/barcode_test2.svg';

        $result = $this->barcode->generateSvgFile('TEST123', $filename);

        $this->assertEquals($filename, $result);
        $this->assertFileExists($filename);

        unlink($filename);
    }

    public function test_generate_svg_base64()
    {
        $result = $this->barcode->generateSvgBase64('TEST123');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $result);

        $base64 = mb_substr($result, 26);
        $decoded = base64_decode($base64);

        $this->assertStringContainsString('<svg', $decoded);
        $this->assertStringContainsString('TEST123', $decoded);
    }

    public function test_generate_svg_base64_with_options()
    {
        $options = [
            'bar_width' => 4,
            'show_text' => false,
        ];

        $result = $this->barcode->generateSvgBase64('TEST123', $options);

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $result);

        $base64 = mb_substr($result, 26);
        $decoded = base64_decode($base64);

        $this->assertStringContainsString('<svg', $decoded);
        $this->assertStringNotContainsString('<text', $decoded);
    }

    public function test_all_methods_handle_same_data_consistently()
    {
        $code = 'CONSISTENCY';

        $pattern1 = $this->barcode->generate($code);
        $pattern2 = $this->barcode->getBinaryPattern($code);

        $this->assertEquals($pattern1, $pattern2);

        $svg = $this->barcode->generateSvg($code);
        $this->assertStringContainsString($code, $svg);

        $base64 = $this->barcode->generateSvgBase64($code);
        $decoded = base64_decode(mb_substr($base64, 26));
        $this->assertStringContainsString($code, $decoded);
    }

    public function test_generate_methods_with_complex_data()
    {
        $complexCode = 'ABC123!@#$%^&*()_+-=[]';

        $pattern = $this->barcode->generate($complexCode);
        $this->assertMatchesRegularExpression('/^[01]+$/', $pattern);

        $svg = $this->barcode->generateSvg($complexCode);
        $this->assertStringContainsString('<svg', $svg);

        $this->assertTrue($this->barcode->validateCode($complexCode));
    }

    public function test_svg_methods_propagate_renderer_exceptions()
    {
        $invalidPath = '/invalid/directory/file.svg';

        $this->expectException(RuntimeException::class);

        $this->barcode->generateSvgFile('TEST', $invalidPath);
    }

    public function test_methods_propagate_generator_exceptions()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->barcode->generate('');
    }

    public function test_generate_with_options_uses_renderer_correctly()
    {
        $result = $this->barcode->generateWithOptions('12345678', 1, 30);

        $base64 = mb_substr($result, 26);
        $decoded = base64_decode($base64);

        // Should contain bars with width=1 and height=30
        $this->assertStringContainsString('width="1"', $decoded);
        $this->assertStringContainsString('height="30"', $decoded);
    }
}
