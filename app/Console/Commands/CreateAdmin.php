<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Creates the first admin on a fresh server, or promotes an existing account.
 * The person sets their own password from the email; none is typed here.
 */
#[Signature('shop:create-admin {email : Their email address} {name? : Their full name}')]
#[Description('Create (or promote) an admin and email them a link to set their password')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));

        if (Validator::make(['email' => $email], ['email' => ['required', 'email']])->fails()) {
            $this->error('That doesn\'t look like an email address.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user === null) {
            $name = (string) ($this->argument('name') ?? $this->ask('Their full name'));

            if (trim($name) === '') {
                $this->error('A name is needed for a new account.');

                return self::FAILURE;
            }

            $user = new User;
            $user->forceFill([
                'name' => trim($name),
                'email' => $email,
                'password' => Str::password(40),
                'email_verified_at' => now(),
            ])->save();
        }

        $user->assignRole(Role::Admin->value);

        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            $this->warn("{$email} is an admin, but the password email wasn't sent ({$status}). Check the mail settings, then use \"Forgot password\" on the login page.");

            return self::SUCCESS;
        }

        $this->info("{$email} is an admin. They'll get an email to set their password.");

        return self::SUCCESS;
    }
}
