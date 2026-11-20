<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Closes an account on request (Data Protection Act, 2012): removes the
 * person's name, email and sign-in methods, but keeps the anonymous sales,
 * deposit and request records the business must hold. Users are never deleted.
 */
class AccountCloser
{
    public function close(User $user, User $by): void
    {
        if ($user->is($by)) {
            throw new DomainException('You can\'t close your own account.');
        }

        DB::transaction(function () use ($user) {
            $user->syncRoles([]);
            $user->forceFill([
                'name' => "Closed account #{$user->id}",
                'email' => "closed-{$user->id}@accounts.invalid",
                'password' => Str::password(40),
                'remember_token' => null,
                'email_verified_at' => null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'location_id' => null,
            ])->save();

            DB::table('passkeys')->where('user_id', $user->id)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('notifications')
                ->where('notifiable_type', $user->getMorphClass())
                ->where('notifiable_id', $user->id)
                ->delete();

            // Names and phone numbers staff typed onto the person's sales.
            Sale::query()->where('customer_id', $user->id)->toBase()->update([
                'customer_name' => null,
                'customer_phone' => null,
            ]);
        });
    }

    public function isClosed(User $user): bool
    {
        return str_ends_with($user->email, '@accounts.invalid');
    }
}
