<?php

namespace App\Http\Requests\Api;

use App\Support\BanglaText;

/**
 * Converts Bangla digits and phone formats before validation runs, so a user
 * typing "০১৭১২-৩৪৫৬৭৮" or "+8801712345678" is accepted as 01712345678.
 */
trait NormalizesBanglaInput
{
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('phone')) {
            $merge['phone'] = BanglaText::normalizePhone((string) $this->input('phone'));
        }
        if ($this->has('pin')) {
            $merge['pin'] = BanglaText::toAsciiDigits(trim((string) $this->input('pin')));
        }
        if ($this->has('name')) {
            $merge['name'] = trim((string) $this->input('name'));
        }

        $this->merge($merge);
    }
}
