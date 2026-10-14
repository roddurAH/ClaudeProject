<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Permanently deletes an app user's account and everything they sent:
 * profile, sign-in tokens, questions (with photos) and the expert answers to
 * them. Required by Google Play for apps that let people create an account.
 *
 * Staff accounts are not deleted this way: their answers belong to farmers'
 * questions, so an admin disables them in the admin panel instead.
 */
class AccountDeletionService
{
    public function __construct(private readonly ImageService $images) {}

    /** @throws ValidationException for staff accounts */
    public function delete(User $user, string $errorField = 'pin'): void
    {
        if ($user->isStaff()) {
            throw ValidationException::withMessages([
                $errorField => [__('Staff accounts are removed by an administrator.')],
            ]);
        }

        $photos = $user->questions()->whereNotNull('image_path')->pluck('image_path');

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->delete(); // questions and their answers cascade in the database
        });

        // Files last: if the database step fails nothing is lost.
        $photos->each(fn (string $path) => $this->images->delete($path));
    }
}
