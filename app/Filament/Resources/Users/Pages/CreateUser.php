<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Admin-created accounts (staff, suppliers) get a random password and an
 * email to set their own.
 */
class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [...$data, 'password' => Str::password(40)];
    }

    protected function afterCreate(): void
    {
        $this->record->forceFill(['email_verified_at' => now()])->save();

        Password::sendResetLink(['email' => $this->record->getAttribute('email')]);

        Notification::make()->success()->title('Invitation sent')->body('They\'ll get an email to set their password.')->send();
    }
}
