<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Admin-created accounts (staff, suppliers) either get an email to choose
 * their own password, or a password the admin sets and tells them.
 */
class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $chosen = $this->setsPasswordNow() ? (string) ($this->data['new_password'] ?? '') : '';

        return [...$data, 'password' => $chosen !== '' ? $chosen : Str::password(40)];
    }

    protected function afterCreate(): void
    {
        $this->record->forceFill(['email_verified_at' => now()])->save();

        if ($this->setsPasswordNow()) {
            Notification::make()->success()->title('Account created')
                ->body('Give them their password in person or by phone. They can change it under Settings → Password.')
                ->send();

            return;
        }

        Password::sendResetLink(['email' => $this->record->getAttribute('email')]);

        Notification::make()->success()->title('Invitation sent')->body('They\'ll get an email to set their password.')->send();
    }

    private function setsPasswordNow(): bool
    {
        return ($this->data['password_method'] ?? 'email') === 'set';
    }
}
