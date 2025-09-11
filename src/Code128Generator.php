<?php

declare(strict_types=1);

namespace Barcode;

use InvalidArgumentException;

final class Code128Generator
{
    private const START_A = 103;

    private const START_B = 104;

    private const START_C = 105;

    private const STOP = 106;

    private const PATTERNS = [
        '11011001100', '11001101100', '11001100110', '10010011000',
        '10010001100', '10001001100', '10011001000', '10011000100',
        '10001100100', '11001001000', '11001000100', '11000100100',
        '10110011100', '10011011100', '10011001110', '10111001100',
        '10011101100', '10011100110', '11001110010', '11001011100',
        '11001001110', '11011100100', '11001110100', '11101101110',
        '11101001100', '11100101100', '11100100110', '11101100100',
        '11100110100', '11100110010', '11011011000', '11011000110',
        '11000110110', '10100011000', '10001011000', '10001000110',
        '10110001000', '10001101000', '10001100010', '11010001000',
        '11000101000', '11000100010', '10110111000', '10110001110',
        '10001101110', '10111011000', '10111000110', '10001110110',
        '11101110110', '11010001110', '11000101110', '11011101000',
        '11011100010', '11011101110', '11101011000', '11101000110',
        '11100010110', '11101101000', '11101100010', '11100011010',
        '11101111010', '11001000010', '11110001010', '10100110000',
        '10100001100', '10010110000', '10010000110', '10000101100',
        '10000100110', '10110010000', '10110000100', '10011010000',
        '10011000010', '10000110100', '10000110010', '11000010010',
        '11001010000', '11110111010', '11000010100', '10001111010',
        '10100111100', '10010111100', '10010011110', '10111100100',
        '10011110100', '10011110010', '11110100100', '11110010100',
        '11110010010', '11011011110', '11011110110', '11110110110',
        '10101111000', '10100011110', '10001011110', '10111101000',
        '10111100010', '11110101000', '11110100010', '10111011110',
        '10111101110', '11101011110', '11110101110', '11010000100',
        '11010010000', '11010011100', '1100011101011',
    ];

    private const CODE_SET_A = [
        ' ', '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?',
        '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
        'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', '\\', ']', '^', '_',
    ];

    private const CODE_SET_B = [
        ' ', '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?',
        '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
        'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', '\\', ']', '^', '_',
        '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o',
        'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '{', '|', '}', '~',
    ];

    public function generate(string $data): string
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Data cannot be empty');
        }

        if (mb_strlen($data) > 48) {
            throw new InvalidArgumentException('Data too long (max 48 characters)');
        }

        if (! $this->isValidAscii($data)) {
            throw new InvalidArgumentException('Data contains non-ASCII characters. Code 128 only supports ASCII characters (0-127)');
        }

        $codeSet = $this->determineOptimalCodeSet($data);
        $values = $this->convertToValues($data, $codeSet);
        $checksum = $this->calculateChecksum($values, $codeSet);

        return $this->generateBinaryPattern($values, $codeSet, $checksum);
    }

    public function getPatternWidth(string $pattern): int
    {
        return mb_strlen($pattern);
    }

    public function validateData(string $data): bool
    {
        if (empty($data) || mb_strlen($data) > 48) {
            return false;
        }

        $codeSet = $this->determineOptimalCodeSet($data);

        try {
            $this->convertToValues($data, $codeSet);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    private function determineOptimalCodeSet(string $data): string
    {
        if (ctype_digit($data) && mb_strlen($data) % 2 === 0 && mb_strlen($data) >= 4) {
            return 'C';
        }

        $hasLowercase = preg_match('/[a-z]/', $data);

        return $hasLowercase ? 'B' : 'A';
    }

    /**
     * @return array<int>
     */
    private function convertToValues(string $data, string $codeSet): array
    {
        $values = [];

        if ($codeSet === 'C') {
            for ($i = 0; $i < mb_strlen($data); $i += 2) {
                $values[] = (int) mb_substr($data, $i, 2);
            }
        } else {
            $charSet = $codeSet === 'A' ? self::CODE_SET_A : self::CODE_SET_B;

            for ($i = 0; $i < mb_strlen($data); $i++) {
                $char = mb_substr($data, $i, 1);
                $value = array_search($char, $charSet, true);

                if ($value === false) {
                    throw new InvalidArgumentException("Character '{$char}' not supported in Code Set {$codeSet}");
                }

                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * @param  array<int>  $values
     */
    private function calculateChecksum(array $values, string $codeSet): int
    {
        $startValue = match ($codeSet) {
            'A' => self::START_A,
            'B' => self::START_B,
            'C' => self::START_C,
            default => throw new InvalidArgumentException("Invalid code set: {$codeSet}"),
        };

        $checksum = $startValue;

        foreach ($values as $position => $value) {
            $checksum += (int) $value * ($position + 1);
        }

        return $checksum % 103;
    }

    /**
     * @param  array<int>  $values
     */
    private function generateBinaryPattern(array $values, string $codeSet, int $checksum): string
    {
        $startCode = match ($codeSet) {
            'A' => self::START_A,
            'B' => self::START_B,
            'C' => self::START_C,
            default => throw new InvalidArgumentException("Invalid code set: {$codeSet}"),
        };

        $pattern = self::PATTERNS[$startCode];

        foreach ($values as $value) {
            $pattern .= self::PATTERNS[$value];
        }

        $pattern .= self::PATTERNS[$checksum];
        $pattern .= self::PATTERNS[self::STOP];

        return $pattern;
    }

    private function isValidAscii(string $data): bool
    {
        for ($i = 0; $i < mb_strlen($data); $i++) {
            $char = ord($data[$i]);
            if ($char > 127) {
                return false;
            }
        }

        return true;
    }
}
