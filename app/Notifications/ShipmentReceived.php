<?php

namespace App\Notifications;

use App\Enums\ReceiptOutcome;
use App\Filament\Resources\Shipments\ShipmentResource;
use App\Models\Shipment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the admin every item on a shipment has been checked in, and flags problems.
 */
class ShipmentReceived extends Notification implements ShouldQueue
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
        $mail = (new MailMessage)
            ->subject("Shipment {$this->shipment->reference} has been checked in")
            ->greeting("Hello {$notifiable->name},")
            ->line($this->summary());

        if ($this->issueCount() > 0) {
            $mail->line('Some items need your attention.');
        }

        return $mail->action('Review the shipment', $this->url());
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        $notification = FilamentNotification::make()
            ->title("{$this->shipment->reference} has been checked in")
            ->body($this->summary())
            ->actions([Action::make('view')->label('Review')->url($this->url())]);

        $this->issueCount() > 0 ? $notification->warning() : $notification->success();

        return [
            ...$notification->getDatabaseMessage(),
            'url' => $this->url(),
            'shipment_id' => $this->shipment->id,
        ];
    }

    private function summary(): string
    {
        $counts = $this->shipment->items()->toBase()
            ->selectRaw('outcome, count(*) as total')
            ->groupBy('outcome')
            ->pluck('total', 'outcome');

        return collect(ReceiptOutcome::cases())
            ->filter(fn (ReceiptOutcome $o) => ($counts[$o->value] ?? 0) > 0)
            ->map(fn (ReceiptOutcome $o) => "{$counts[$o->value]} {$o->getLabel()}")
            ->implode(', ').'.';
    }

    private function issueCount(): int
    {
        return $this->shipment->items()
            ->whereIn('outcome', [ReceiptOutcome::Damaged, ReceiptOutcome::Missing])
            ->count();
    }

    private function url(): string
    {
        return ShipmentResource::getUrl('edit', ['record' => $this->shipment], panel: 'admin');
    }
}
