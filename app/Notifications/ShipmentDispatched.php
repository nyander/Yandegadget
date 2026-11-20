<?php

namespace App\Notifications;

use App\Models\Shipment;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells staff in Ghana a shipment is on its way ("NewShipment" in 2020).
 */
class ShipmentDispatched extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Shipment $shipment) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $shipment = $this->shipment->loadMissing(['carrier', 'destination'])->loadCount('items');

        return (new MailMessage)
            ->subject("Shipment {$shipment->reference} is on its way")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$shipment->items_count} item(s) have left the UK for {$shipment->destination->name} with {$shipment->carrier?->name}.")
            ->line($shipment->expected_on ? 'Expected around '.$shipment->expected_on->format('j F Y').'.' : 'No arrival date was given.')
            ->action('Open the shipment', $this->url())
            ->line('Check each item in as it arrives.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        $shipment = $this->shipment->loadCount('items');

        return [
            ...FilamentNotification::make()
                ->title("{$shipment->reference} is on its way")
                ->body("{$shipment->items_count} item(s) to check in when it arrives.")
                ->info()
                ->getDatabaseMessage(),
            'url' => $this->url(),
            'shipment_id' => $shipment->id,
        ];
    }

    private function url(): string
    {
        return route('staff.shipments.show', $this->shipment);
    }
}
