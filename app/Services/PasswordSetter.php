<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PasswordSetByShop;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * An admin setting someone else's password, for staff who can't easily use
 * email. Signs them out everywhere and tells them by email.
 */
class PasswordSetter
{
    public function set(User $user, string $password, User $by): void
    {
        if ($user->is($by)) {
            throw new DomainException('Change your own password under Settings → Password.');
        }

        if ($user->isClosed()) {
            throw new DomainException('This account is closed.');
        }

        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ])->save();

        // Signed in elsewhere with the old password? Not any more.
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $user->notify(new PasswordSetByShop($by->name));
    }

    /**
     * Emails a link to choose their own password (the "forgot password" email).
     */
    public function sendLink(User $user): string
    {
        return Password::sendResetLink(['email' => $user->email]);
    }
}
