<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\AccountCloser;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
