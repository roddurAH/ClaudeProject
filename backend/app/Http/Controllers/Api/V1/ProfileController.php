<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DeleteAccountRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AccountDeletionService;
use App\Services\PinAuthenticator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $data = $request->validated();

        if (isset($data['pin'])) {
            if (! Hash::check((string) $data['current_pin'], $user->password)) {
                throw ValidationException::withMessages(['current_pin' => [__('Current PIN is incorrect.')]]);
            }
            $user->password = $data['pin'];
        }

        unset($data['pin'], $data['current_pin']);
        $user->fill($data)->save();

        return new UserResource($user);
    }

    /** Permanently deletes the signed-in user's account (PIN required). */
    public function destroy(DeleteAccountRequest $request, PinAuthenticator $pins, AccountDeletionService $accounts): JsonResponse
    {
        $user = $request->user();
        $pins->verify($user->phone, $request->validated('pin'), 'pin', __('PIN is incorrect.'));
        $accounts->delete($user);

        return response()->json(['message' => __('Your account and all your data have been deleted.')]);
    }
}
