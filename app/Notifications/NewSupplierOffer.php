<?php

namespace App\Notifications;

use App\Filament\Resources\SupplierOffers\SupplierOfferResource;
use App\Models\SupplierOffer;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the shop a supplier has offered an item ("NewSupplierProduct" in 2020).
 */
class NewSupplierOffer extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SupplierOffer $offer) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $offer = $this->offer->loadMissing(['supplier', 'condition']);

        return (new MailMessage)
            ->subject("New offer {$offer->reference}: {$offer->title}")
            ->line("{$offer->supplier->name} is offering {$offer->title} (grade {$offer->condition->grade}) for {$offer->asking_price->formatToLocale('en_GB')}.")
            ->action('Review the offer', $this->url());
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        $offer = $this->offer->loadMissing('supplier');

        return [
            ...FilamentNotification::make()
                ->title("New offer from {$offer->supplier->name}")
                ->body("{$offer->title} for {$offer->asking_price->formatToLocale('en_GB')}")
                ->info()
                ->actions([Action::make('view')->label('Review')->url($this->url())])
                ->getDatabaseMessage(),
            'url' => $this->url(),
            'supplier_offer_id' => $offer->id,
        ];
    }

    private function url(): string
    {
        return SupplierOfferResource::getUrl('index', panel: 'admin').'?tableSearch='.urlencode($this->offer->reference);
    }
}
