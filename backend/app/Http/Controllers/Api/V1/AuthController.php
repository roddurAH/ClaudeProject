<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\PinAuthenticator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Phone + PIN authentication for the mobile app.
 *
 * Why not SMS OTP? It costs money per message and needs an SMS gateway
 * contract. A 4–6 digit PIN (like bKash/Nagad) is familiar to rural users.
 * Brute force is limited per phone number (see PinAuthenticator).
 * An OTP step can be added later in register() without changing the app flow.
 */
class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'password' => $data['pin'],
            'district_id' => $data['district_id'] ?? null,
            'user_type' => $data['user_type'] ?? 'farmer',
            'preferred_language' => $data['preferred_language'] ?? 'bn',
        ]);
        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'token' => $this->issueToken($user, $data['device_name'] ?? null),
            'user' => new UserResource($user->refresh()),
        ], 201);
    }

    public function login(LoginRequest $request, PinAuthenticator $pins): JsonResponse
    {
        $data = $request->validated();
        $user = $pins->verify($data['phone'], $data['pin']);

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'phone' => [__('This account has been disabled. Please contact support.')],
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'token' => $this->issueToken($user, $data['device_name'] ?? null),
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => __('Logged out.')]);
    }

    private function issueToken(User $user, ?string $deviceName): string
    {
        return $user->createToken(
            $deviceName ?: 'mobile-app',
            ['app'],
            now()->addDays(config('krishi.token_ttl_days')),
        )->plainTextToken;
    }
}
