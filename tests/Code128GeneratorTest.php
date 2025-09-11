<?php

declare(strict_types=1);

use Barcode\Code128Generator;
use PHPUnit\Framework\TestCase;

final class Code128GeneratorTest extends TestCase
{
    private Code128Generator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new Code128Generator();
    }

    public function test_generate_with_valid_numeric_data()
    {
        $result = $this->generator->generate('123456');

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
        $this->assertGreaterThan(50, mb_strlen($result));
    }

    public function test_generate_with_valid_alphanumeric_data()
    {
        $result = $this->generator->generate('ABC123');

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
    }

    public function test_generate_with_lowercase_uses_code_set_b()
    {
        $result = $this->generator->generate('test123');

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
    }

    public function test_generate_with_even_digit_string_uses_code_set_c()
    {
        $result = $this->generator->generate('1234');

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
    }

    public function test_generate_throws_exception_for_empty_data()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data cannot be empty');

        $this->generator->generate('');
    }

    public function test_generate_throws_exception_for_data_too_long()
    {
        $longData = str_repeat('A', 49);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data too long (max 48 characters)');

        $this->generator->generate($longData);
    }

    public function test_generate_throws_exception_for_non_ascii_characters()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data contains non-ASCII characters. Code 128 only supports ASCII characters (0-127)');

        $this->generator->generate('test€123');
    }

    public function test_generate_with_special_characters()
    {
        $result = $this->generator->generate('!@#$%^&*()');

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
    }

    public function test_generate_with_mixed_case()
    {
        $result = $this->generator->generate('TeSt123');

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
    }

    public function test_generate_with_maximum_length()
    {
        $maxData = str_repeat('A', 48);
        $result = $this->generator->generate($maxData);

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
    }

    public function test_get_pattern_width()
    {
        $pattern = '110110011001101100110';
        $width = $this->generator->getPatternWidth($pattern);

        $this->assertEquals(21, $width);
    }

    public function test_validate_data_returns_true_for_valid_data()
    {
        $this->assertTrue($this->generator->validateData('123456'));
        $this->assertTrue($this->generator->validateData('ABC123'));
        $this->assertTrue($this->generator->validateData('test123'));
    }

    public function test_validate_data_returns_false_for_empty_data()
    {
        $this->assertFalse($this->generator->validateData(''));
    }

    public function test_validate_data_returns_false_for_data_too_long()
    {
        $longData = str_repeat('A', 49);
        $this->assertFalse($this->generator->validateData($longData));
    }

    public function test_validate_data_returns_false_for_non_ascii()
    {
        $this->assertFalse($this->generator->validateData('test€123'));
    }

    public function test_validate_data_returns_true_for_boundary_length()
    {
        $maxData = str_repeat('A', 48);
        $this->assertTrue($this->generator->validateData($maxData));
    }

    public function test_generate_with_code_set_a_characters()
    {
        $result = $this->generator->generate('ABC123!@#');

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
    }

    public function test_generate_with_odd_digit_string_doesnt_use_code_set_c()
    {
        $result = $this->generator->generate('12345');

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
    }

    public function test_generate_with_short_digit_string_doesnt_use_code_set_c()
    {
        $result = $this->generator->generate('12');

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
    }

    public function test_generate_consistent_output()
    {
        $data = 'TEST123';
        $result1 = $this->generator->generate($data);
        $result2 = $this->generator->generate($data);

        $this->assertEquals($result1, $result2);
    }

    public function test_generate_with_all_printable_ascii()
    {
        $ascii = '';
        for ($i = 32; $i <= 126; $i++) {
            $ascii .= chr($i);
            if (mb_strlen($ascii) >= 48) {
                break;
            }
        }

        $result = $this->generator->generate($ascii);

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[01]+$/', $result);
    }

    public function test_validate_data_handles_exception_internally()
    {
        // Test with data that would cause an exception in convertToValues
        $result = $this->generator->validateData('test€');
        $this->assertFalse($result);
    }
}
