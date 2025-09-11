<?php

declare(strict_types=1);

use Barcode\SvgRenderer;
use PHPUnit\Framework\TestCase;

final class SvgRendererTest extends TestCase
{
    private SvgRenderer $renderer;

    private string $testPattern = '11011001100110110011001100110011001100';

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new SvgRenderer();
    }

    public function test_constructor_with_default_options()
    {
        $renderer = new SvgRenderer();
        $svg = $renderer->render($this->testPattern, 'TEST');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
    }

    public function test_constructor_with_custom_options()
    {
        $options = [
            'bar_width' => 3,
            'bar_height' => 80,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'background_color' => '#FF0000',
            'foreground_color' => '#FFFFFF',
            'show_text' => false,
            'text_size' => 16,
            'text_margin' => 8,
        ];

        $renderer = new SvgRenderer($options);
        $svg = $renderer->render($this->testPattern, 'TEST');

        $this->assertStringContainsString('fill="#FF0000"', $svg);
        $this->assertStringContainsString('fill="#FFFFFF"', $svg);
        $this->assertStringNotContainsString('<text', $svg);
    }

    public function test_constructor_handles_invalid_option_types()
    {
        $options = [
            'bar_width' => 'invalid',
            'bar_height' => null,
            'background_color' => 123,
            'show_text' => 'true',
        ];

        $renderer = new SvgRenderer($options);
        $svg = $renderer->render($this->testPattern, 'TEST');

        // Should use default values
        $this->assertStringContainsString('<svg', $svg);
    }

    public function test_render_basic_svg()
    {
        $svg = $this->renderer->render($this->testPattern, 'TEST123');

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringEndsWith('</svg>', $svg);
        $this->assertStringContainsString('<rect', $svg);
        $this->assertStringContainsString('<text', $svg);
        $this->assertStringContainsString('TEST123', $svg);
    }

    public function test_render_without_text()
    {
        $svg = $this->renderer->render($this->testPattern);

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringEndsWith('</svg>', $svg);
        $this->assertStringNotContainsString('<text', $svg);
    }

    public function test_render_with_text_disabled()
    {
        $renderer = new SvgRenderer(['show_text' => false]);
        $svg = $renderer->render($this->testPattern, 'TEST123');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringNotContainsString('<text', $svg);
        $this->assertStringNotContainsString('TEST123', $svg);
    }

    public function test_render_to_base64()
    {
        $result = $this->renderer->renderToBase64($this->testPattern, 'TEST');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $result);

        $base64 = mb_substr($result, 26);
        $decoded = base64_decode($base64);

        $this->assertStringContainsString('<svg', $decoded);
        $this->assertStringContainsString('TEST', $decoded);
    }

    public function test_render_to_file_success()
    {
        $filename = sys_get_temp_dir().'/test_barcode.svg';

        $result = $this->renderer->renderToFile($this->testPattern, $filename, 'TEST');

        $this->assertEquals($filename, $result);
        $this->assertFileExists($filename);

        $content = file_get_contents($filename);
        $this->assertStringContainsString('<svg', $content);
        $this->assertStringContainsString('TEST', $content);

        unlink($filename);
    }

    public function test_render_to_file_failure()
    {
        $invalidPath = '/invalid/path/test.svg';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid file path provided');

        $this->renderer->renderToFile($this->testPattern, $invalidPath, 'TEST');
    }

    public function test_set_bar_width()
    {
        $result = $this->renderer->setBarWidth(5);

        $this->assertSame($this->renderer, $result);

        // Test minimum value enforcement
        $this->renderer->setBarWidth(0);
        $svg = $this->renderer->render($this->testPattern);
        $this->assertStringContainsString('width="1"', $svg);
    }

    public function test_set_bar_height()
    {
        $result = $this->renderer->setBarHeight(100);

        $this->assertSame($this->renderer, $result);

        // Test minimum value enforcement
        $this->renderer->setBarHeight(5);
        $svg = $this->renderer->render($this->testPattern);
        $this->assertStringContainsString('height="10"', $svg);
    }

    public function test_set_margins()
    {
        $result = $this->renderer->setMargins(20, 25, 30, 35);

        $this->assertSame($this->renderer, $result);

        // Test with negative values (should be set to 0)
        $this->renderer->setMargins(-5, -10, -15, -20);
        $svg = $this->renderer->render($this->testPattern);
        $this->assertStringContainsString('<svg', $svg);
    }

    public function test_set_colors()
    {
        $result = $this->renderer->setColors('#123456', '#ABCDEF');

        $this->assertSame($this->renderer, $result);

        $svg = $this->renderer->render($this->testPattern, 'TEST');
        $this->assertStringContainsString('fill="#123456"', $svg);
        $this->assertStringContainsString('fill="#ABCDEF"', $svg);
    }

    public function test_set_show_text()
    {
        $result = $this->renderer->setShowText(false);

        $this->assertSame($this->renderer, $result);

        $svg = $this->renderer->render($this->testPattern, 'TEST');
        $this->assertStringNotContainsString('<text', $svg);

        $this->renderer->setShowText(true);
        $svg = $this->renderer->render($this->testPattern, 'TEST');
        $this->assertStringContainsString('<text', $svg);
    }

    public function test_render_with_special_characters_in_text()
    {
        $svg = $this->renderer->render($this->testPattern, '<>&"\'');

        $this->assertStringContainsString('&lt;&gt;&amp;"\'', $svg);
    }

    public function test_render_calculates_correct_dimensions()
    {
        $patternLength = mb_strlen($this->testPattern);
        $expectedWidth = ($patternLength * 2) + 10 + 10; // default bar_width=2, margins=10

        $svg = $this->renderer->render($this->testPattern, 'TEST');

        $this->assertStringContainsString("width=\"{$expectedWidth}\"", $svg);
    }

    public function test_render_with_empty_pattern()
    {
        $svg = $this->renderer->render('', 'TEST');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('TEST', $svg);
    }

    public function test_fluent_interface()
    {
        $result = $this->renderer
            ->setBarWidth(3)
            ->setBarHeight(80)
            ->setMargins(5, 5, 5, 5)
            ->setColors('#000000', '#FFFFFF')
            ->setShowText(true);

        $this->assertSame($this->renderer, $result);
    }

    public function test_render_with_custom_text_options()
    {
        $renderer = new SvgRenderer([
            'text_size' => 20,
            'text_margin' => 10,
        ]);

        $svg = $renderer->render($this->testPattern, 'CUSTOM');

        $this->assertStringContainsString('font-size="20"', $svg);
        $this->assertStringContainsString('CUSTOM', $svg);
    }

    public function test_background_renders_correctly()
    {
        $renderer = new SvgRenderer(['background_color' => '#FF00FF']);
        $svg = $renderer->render($this->testPattern);

        // Background should be the first rect
        $this->assertStringContainsString('<rect x="0" y="0"', $svg);
        $this->assertStringContainsString('fill="#FF00FF"', $svg);
    }
}
