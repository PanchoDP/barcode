<?php

declare(strict_types=1);

namespace Barcode;

final class Barcode
{
    private Code128Generator $generator;

    public function __construct()
    {
        $this->generator = new Code128Generator();
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function generate(string $code, array $options = []): string
    {
        return $this->generator->generate($code);
    }

    public function generateWithOptions(string $code, int $width = 2, int $height = 60): string
    {
        $pattern = $this->generator->generate($code);

        $renderer = new SvgRenderer([
            'bar_width' => $width,
            'bar_height' => $height,
        ]);

        return $renderer->renderToBase64($pattern, $code);
    }

    public function validateCode(string $code): bool
    {
        return $this->generator->validateData($code);
    }

    public function getBinaryPattern(string $code): string
    {
        return $this->generator->generate($code);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function generateSvg(string $code, string $text = '', array $options = []): string
    {
        $pattern = $this->generator->generate($code);
        $renderer = new SvgRenderer($options);

        return $renderer->render($pattern, $text ?: $code);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function generateSvgFile(string $code, string $filename, array $options = []): string
    {
        $pattern = $this->generator->generate($code);
        $renderer = new SvgRenderer($options);

        return $renderer->renderToFile($pattern, $filename, $code);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function generateSvgBase64(string $code, array $options = []): string
    {
        $pattern = $this->generator->generate($code);
        $renderer = new SvgRenderer($options);

        return $renderer->renderToBase64($pattern, $code);
    }
}
