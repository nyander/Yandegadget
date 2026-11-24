<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\AccountCloser;
use App\Services\PasswordSetter;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Password;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('setPassword')
                ->label('Set password')
                ->icon(Heroicon::OutlinedKey)
                ->color('gray')
                ->modalHeading(fn (User $record) => "Set a password for {$record->name}")
                ->modalDescription('For when they can\'t use the email link. They\'re signed out on their other devices and get an email saying the shop changed it (without the password).')
                ->modalSubmitActionLabel('Set password')
                ->schema([UserResource::passwordInput()->required()])
                ->hidden(fn (User $record) => $record->is(auth()->user()) || $record->isClosed())
                ->action(function (User $record, array $data, PasswordSetter $passwords): void {
                    /** @var User $admin */
                    $admin = auth()->user();
                    $passwords->set($record, (string) $data['password'], $admin);

                    Notification::make()->success()->title('Password set')
                        ->body("Give it to {$record->name} in person or by phone. They can change it under Settings → Password.")
                        ->send();
                }),
            Action::make('sendPasswordLink')
                ->label('Email a password link')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Email a password link?')
                ->modalDescription(fn (User $record) => "{$record->email} gets a link to choose a new password. It works for 60 minutes. Their current password keeps working until they change it.")
                ->modalSubmitActionLabel('Send link')
                ->hidden(fn (User $record) => $record->isClosed())
                ->action(function (User $record, PasswordSetter $passwords): void {
                    $status = $passwords->sendLink($record);

                    $status === Password::RESET_LINK_SENT
                        ? Notification::make()->success()->title('Link sent')->body("Sent to {$record->email}.")->send()
                        : Notification::make()->warning()->title('Not sent')->body(__($status))->send();
                }),
            Action::make('closeAccount')
                ->label('Close account')
                ->icon(Heroicon::OutlinedUserMinus)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Close this account?')
                ->modalDescription('Use this when the person asks us to delete their data. Their name, email, sign-in methods and roles are removed and they can no longer log in. Sales, deposits and requests stay, without their name. This can\'t be undone.')
                ->modalSubmitActionLabel('Close account')
                ->hidden(fn (User $record) => $record->is(auth()->user()) || app(AccountCloser::class)->isClosed($record))
                ->action(function (User $record) {
                    /** @var User $admin */
                    $admin = auth()->user();
                    app(AccountCloser::class)->close($record, $admin);

                    Notification::make()->success()->title('Account closed')
                        ->body('It\'s hidden from the list now. Filter by "Closed accounts" to see it.')
                        ->send();
                    $this->redirect(UserResource::getUrl('index'));
                }),
        ];
    }
}
