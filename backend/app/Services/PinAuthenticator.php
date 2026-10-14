<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Checks a phone number + PIN pair. Wrong PINs are counted per phone number,
 * so guessing is blocked for 15 minutes after 5 mistakes, whether the guesses
 * come from the app login, the in-app account deletion or the web form.
 */
class PinAuthenticator
{
    public const MAX_ATTEMPTS = 5;

    public const LOCKOUT_SECONDS = 900; // 15 minutes

    /** @throws ValidationException when locked out or the PIN does not match */
    public function verify(string $phone, string $pin, string $errorField = 'phone', ?string $wrongPinMessage = null): User
    {
        $key = 'login:'.$phone;

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            throw ValidationException::withMessages([
                $errorField => [__('Too many wrong attempts. Try again in :minutes minutes.', ['minutes' => $minutes])],
            ])->status(429);
        }

        $user = User::where('phone', $phone)->first();

        if (! $user || ! Hash::check($pin, $user->password)) {
            RateLimiter::hit($key, self::LOCKOUT_SECONDS);
            throw ValidationException::withMessages([
                $errorField => [$wrongPinMessage ?? __('Mobile number or PIN is incorrect.')],
            ]);
        }

        RateLimiter::clear($key);

        return $user;
    }
}
