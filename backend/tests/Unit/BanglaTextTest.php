<?php

namespace Tests\Unit;

use App\Support\BanglaText;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BanglaTextTest extends TestCase
{
    public function test_converts_bangla_digits(): void
    {
        $this->assertSame('0123456789', BanglaText::toAsciiDigits('০১২৩৪৫৬৭৮৯'));
    }

    #[DataProvider('phones')]
    public function test_normalizes_phone_numbers(string $input, string $expected): void
    {
        $this->assertSame($expected, BanglaText::normalizePhone($input));
    }

    public static function phones(): array
    {
        return [
            'local' => ['01712345678', '01712345678'],
            'with +880' => ['+8801712345678', '01712345678'],
            'with 880' => ['8801712345678', '01712345678'],
            'spaces and dashes' => ['017-1234 5678', '01712345678'],
            'bangla digits' => ['০১৭১২৩৪৫৬৭৮', '01712345678'],
        ];
    }

    public function test_phone_regex(): void
    {
        $this->assertMatchesRegularExpression(BanglaText::PHONE_REGEX, '01912345678');
        $this->assertDoesNotMatchRegularExpression(BanglaText::PHONE_REGEX, '01212345678'); // no 012 operator
        $this->assertDoesNotMatchRegularExpression(BanglaText::PHONE_REGEX, '0171234567');  // too short
    }
}
