<?php

namespace App\Notifications;

use App\Enums\ReservationPayment;
use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Reservation;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the shop a customer has reserved and paid for an item online.
 */
class NewReservation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Reservation $reservation) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title())
            ->line($this->body())
            ->line('Keep the item aside. It\'s marked Reserved, so it can only be sold to them.')
            ->action('Open the reservation', $this->url($notifiable));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        return [
            ...FilamentNotification::make()
                ->title($this->title())
                ->body($this->body())
                ->success()
                ->actions([Action::make('view')->label('Open')->url($this->url($notifiable))])
                ->getDatabaseMessage(),
            'url' => $this->url($notifiable),
            'reservation_id' => $this->reservation->id,
        ];
    }

    private function title(): string
    {
        return "Reserved online: {$this->reservation->reference}";
    }

    private function body(): string
    {
        $reservation = $this->reservation->loadMissing(['stockUnit.product', 'customer']);
        $locale = (string) config('shop.display_locale');
        $how = $reservation->payment_option === ReservationPayment::Full
            ? 'paid in full ('.$reservation->due->formatToLocale($locale).')'
            : 'paid a deposit of '.$reservation->due->formatToLocale($locale);

        return "{$reservation->customer->name} {$how} for {$reservation->stockUnit->product->name} ({$reservation->stockUnit->reference}). Collect by {$reservation->collect_by?->format('D j M')}.";
    }

    private function url(User $notifiable): string
    {
        return $notifiable->canAccessPanel(Filament::getPanel('admin'))
            ? ReservationResource::getUrl('view', ['record' => $this->reservation], panel: 'admin')
            : route('staff.sell.create', $this->reservation->stockUnit);
    }
}
