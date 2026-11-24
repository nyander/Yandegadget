<?php

namespace App\Filament\Resources\Shipments\Pages;

use App\Exceptions\ShipmentRuleViolation;
use App\Filament\Resources\Shipments\ShipmentResource;
use App\Filament\Support\MoneyInput;
use App\Models\Carrier;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

/**
 * @property Shipment $record
 */
class EditShipment extends EditRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('track')
                ->label('Track')
                ->icon(Heroicon::OutlinedMapPin)
                ->color('gray')
                ->url(fn () => $this->record->trackingUrl(), shouldOpenInNewTab: true)
                ->visible(fn () => $this->record->trackingUrl() !== null),
            $this->dispatchAction(),
            DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->record->isDraft() ? parent::getFormActions() : [];
    }

    private function dispatchAction(): Action
    {
        return Action::make('dispatch')
            ->label('Dispatch')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('primary')
            ->visible(fn () => $this->record->isDraft())
            ->disabled(fn () => $this->record->items()->doesntExist())
            ->tooltip(fn () => $this->record->items()->doesntExist() ? 'Add items first' : null)
            ->modalDescription('Every item on this shipment will be marked as in transit, and staff in Ghana will be told it is on its way.')
            ->schema([
                Select::make('carrier_id')
                    ->label('Carrier')
                    ->options(fn () => Carrier::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                    ->required(),
                MoneyInput::make('cost', (string) config('shop.list_currency'))
                    ->label('Shipping cost')
                    ->required(),
                DatePicker::make('dispatched_on')
                    ->label('Dispatched on')
                    ->default(now())
                    ->maxDate(now())
                    ->required(),
                DatePicker::make('expected_on')
                    ->label('Expected arrival')
                    ->afterOrEqual('dispatched_on'),
                TextInput::make('tracking_number')->maxLength(255),
            ])
            ->action(
                function (array $data, ShipmentService $shipments): void {
                    /** @var User $user */
                    $user = auth()->user();

                    try {
                        $shipments->dispatch($this->record, $data, $user);
                    } catch (ShipmentRuleViolation $e) {
                        Notification::make()->danger()->title($e->getMessage())->send();

                        return;
                    }

                    Notification::make()->success()->title("{$this->record->reference} dispatched")->send();
                    $this->redirect(ShipmentResource::getUrl('edit', ['record' => $this->record]));
                });
    }
}
