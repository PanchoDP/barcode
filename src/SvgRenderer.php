<?php

declare(strict_types=1);

namespace Barcode;

use RuntimeException;

final class SvgRenderer
{
    private int $barWidth;

    private int $barHeight;

    private int $marginLeft;

    private int $marginRight;

    private int $marginTop;

    private int $marginBottom;

    private string $backgroundColor;

    private string $foregroundColor;

    private bool $showText;

    private int $textSize;

    private int $textMargin;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(array $options = [])
    {
        // Security: Apply resource limits to prevent DoS
        $this->barWidth = max(1, min(is_int($options['bar_width'] ?? null) ? $options['bar_width'] : 2, 50));
        $this->barHeight = max(10, min(is_int($options['bar_height'] ?? null) ? $options['bar_height'] : 60, 500));
        $this->marginLeft = max(0, min(is_int($options['margin_left'] ?? null) ? $options['margin_left'] : 10, 100));
        $this->marginRight = max(0, min(is_int($options['margin_right'] ?? null) ? $options['margin_right'] : 10, 100));
        $this->marginTop = max(0, min(is_int($options['margin_top'] ?? null) ? $options['margin_top'] : 10, 100));
        $this->marginBottom = max(0, min(is_int($options['margin_bottom'] ?? null) ? $options['margin_bottom'] : 10, 100));

        // Security: Validate colors in constructor
        $backgroundColor = is_string($options['background_color'] ?? null) ? $options['background_color'] : '#FFFFFF';
        $foregroundColor = is_string($options['foreground_color'] ?? null) ? $options['foreground_color'] : '#000000';

        if (! $this->isValidColor($backgroundColor)) {
            throw new RuntimeException('Invalid background color format in constructor');
        }
        if (! $this->isValidColor($foregroundColor)) {
            throw new RuntimeException('Invalid foreground color format in constructor');
        }

        $this->backgroundColor = $backgroundColor;
        $this->foregroundColor = $foregroundColor;
        $this->showText = is_bool($options['show_text'] ?? null) ? $options['show_text'] : true;
        $this->textSize = max(8, min(is_int($options['text_size'] ?? null) ? $options['text_size'] : 12, 72));
        $this->textMargin = max(0, min(is_int($options['text_margin'] ?? null) ? $options['text_margin'] : 5, 50));
    }

    public function render(string $pattern, string $text = ''): string
    {
        // Security: Validate pattern to prevent DoS
        $patternWidth = mb_strlen($pattern);
        if ($patternWidth > 500) {
            throw new RuntimeException('Pattern too long (max 500 characters)');
        }

        if ($text && mb_strlen($text) > 100) {
            throw new RuntimeException('Text too long (max 100 characters)');
        }

        $svgWidth = ($patternWidth * $this->barWidth) + $this->marginLeft + $this->marginRight;

        // Security: Prevent integer overflow and excessive memory usage
        if ($svgWidth > 50000) {
            throw new RuntimeException('Generated SVG would be too large');
        }

        $textHeight = $this->showText && ! empty($text) ? $this->textSize + $this->textMargin : 0;
        $svgHeight = $this->barHeight + $this->marginTop + $this->marginBottom + $textHeight;

        if ($svgHeight > 10000) {
            throw new RuntimeException('Generated SVG would be too large');
        }

        $svg = $this->getSvgHeader($svgWidth, $svgHeight);
        $svg .= $this->renderBackground($svgWidth, $svgHeight);
        $svg .= $this->renderBars($pattern);

        if ($this->showText && ! empty($text)) {
            $svg .= $this->renderText($text, $svgWidth);
        }

        $svg .= $this->getSvgFooter();

        return $svg;
    }

    public function renderToFile(string $pattern, string $filename, string $text = ''): string
    {
        $svg = $this->render($pattern, $text);

        // Security: Prevent path traversal attacks
        $realpath = realpath(dirname($filename));
        if ($realpath === false || mb_strpos($realpath, '..') !== false) {
            throw new RuntimeException('Invalid file path provided');
        }

        // Security: Validate filename contains only safe characters
        $basename = basename($filename);
        if (! preg_match('/^[a-zA-Z0-9_.-]+\.svg$/i', $basename)) {
            throw new RuntimeException('Invalid filename format');
        }

        $dir = dirname($filename);
        if (! is_dir($dir)) {
            throw new RuntimeException('Directory does not exist');
        }

        $result = file_put_contents($filename, $svg);

        if ($result === false) {
            throw new RuntimeException('Failed to save SVG file');
        }

        return $filename;
    }

    public function renderToBase64(string $pattern, string $text = ''): string
    {
        $svg = $this->render($pattern, $text);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function setBarWidth(int $width): self
    {
        // Security: Apply same limits as constructor
        $this->barWidth = max(1, min($width, 50));

        return $this;
    }

    public function setBarHeight(int $height): self
    {
        // Security: Apply same limits as constructor
        $this->barHeight = max(10, min($height, 500));

        return $this;
    }

    public function setMargins(int $left, int $right, int $top, int $bottom): self
    {
        // Security: Apply same limits as constructor
        $this->marginLeft = max(0, min($left, 100));
        $this->marginRight = max(0, min($right, 100));
        $this->marginTop = max(0, min($top, 100));
        $this->marginBottom = max(0, min($bottom, 100));

        return $this;
    }

    public function setColors(string $backgroundColor, string $foregroundColor): self
    {
        // Security: Validate color format to prevent injection
        if (! $this->isValidColor($backgroundColor)) {
            throw new RuntimeException('Invalid background color format');
        }
        if (! $this->isValidColor($foregroundColor)) {
            throw new RuntimeException('Invalid foreground color format');
        }

        $this->backgroundColor = $backgroundColor;
        $this->foregroundColor = $foregroundColor;

        return $this;
    }

    public function setShowText(bool $show): self
    {
        $this->showText = $show;

        return $this;
    }

    private function getSvgHeader(int $width, int $height): string
    {
        return sprintf(
            '<svg width="%d" height="%d" viewBox="0 0 %d %d" xmlns="http://www.w3.org/2000/svg">',
            $width,
            $height,
            $width,
            $height
        );
    }

    private function getSvgFooter(): string
    {
        return '</svg>';
    }

    private function renderBackground(int $width, int $height): string
    {
        return sprintf(
            '<rect x="0" y="0" width="%d" height="%d" fill="%s"/>',
            $width,
            $height,
            $this->backgroundColor
        );
    }

    private function renderBars(string $pattern): string
    {
        $bars = '';
        $x = $this->marginLeft;
        $y = $this->marginTop;

        for ($i = 0; $i < mb_strlen($pattern); $i++) {
            if ($pattern[$i] === '1') {
                $bars .= sprintf(
                    '<rect x="%d" y="%d" width="%d" height="%d" fill="%s"/>',
                    $x,
                    $y,
                    $this->barWidth,
                    $this->barHeight,
                    $this->foregroundColor
                );
            }
            $x += $this->barWidth;
        }

        return $bars;
    }

    private function renderText(string $text, int $svgWidth): string
    {
        $x = $svgWidth / 2;
        $y = $this->marginTop + $this->barHeight + $this->textMargin + $this->textSize;

        return sprintf(
            '<text x="%d" y="%d" font-family="monospace" font-size="%d" text-anchor="middle" fill="%s">%s</text>',
            $x,
            $y,
            $this->textSize,
            $this->foregroundColor,
            htmlspecialchars($text, ENT_XML1, 'UTF-8')
        );
    }

    private function isValidColor(string $color): bool
    {
        // Allow hex colors (#RGB, #RRGGBB) and named colors
        return preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color)
            || in_array(mb_strtolower($color), [
                'red', 'blue', 'green', 'yellow', 'orange', 'purple', 'pink',
                'brown', 'black', 'white', 'gray', 'grey', 'cyan', 'magenta',
            ], true);
    }
}
