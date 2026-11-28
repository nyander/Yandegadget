<?php

namespace App\Notifications;

use App\Enums\ReservationPayment;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the customer their reservation is confirmed, or cancelled and refunded.
 */
class ReservationUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    private ReservationStatus $status;

    public function __construct(public Reservation $reservation)
    {
        // The status at the time, in case it changes before the queue runs.
        $this->status = $reservation->status;
    }

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
            ->subject($this->title())
            ->greeting("Hello {$notifiable->name},");

        foreach ($this->lines() as $line) {
            $mail->line($line);
        }

        return $mail->action('View your reservation', route('reservations.show', $this->reservation));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => implode(' ', $this->lines()),
            'url' => route('reservations.show', $this->reservation),
            'reservation_id' => $this->reservation->id,
            'status' => $this->status->value,
        ];
    }

    private function title(): string
    {
        $ref = $this->reservation->reference;

        return match ($this->status) {
            ReservationStatus::Reserved => "It's yours: collect it by {$this->reservation->collect_by?->format('l j F')} ({$ref})",
            ReservationStatus::Cancelled => "Your reservation {$ref} has been cancelled",
            default => "Update on your reservation {$ref}",
        };
    }

    /**
     * @return list<string>
     */
    private function lines(): array
    {
        $reservation = $this->reservation->loadMissing('stockUnit.product');
        $locale = (string) config('shop.display_locale');
        $item = "{$reservation->stockUnit->product->name} ({$reservation->stockUnit->reference})";
        $paid = $reservation->due->formatToLocale($locale);

        if ($this->status === ReservationStatus::Reserved) {
            $rest = $reservation->payment_option === ReservationPayment::Full
                ? 'It\'s paid in full: just bring your reference.'
                : 'Pay the rest in store: '.$reservation->balance()->formatToLocale($locale).' (the final price can be agreed there).';

            return [
                "Thanks, we've received {$paid}. {$item} is kept for you at 27 Ayawaso, Legon Boundary, Accra.",
                "Please collect it by {$reservation->collect_by?->format('l j F')}. {$rest}",
                'If you can\'t collect it in time, you\'ll get everything back and the item goes back on sale.',
            ];
        }

        if ($this->status === ReservationStatus::Cancelled) {
            $payment = $reservation->paidPayment();

            return array_values(array_filter([
                "Your reservation for {$item} has been cancelled.",
                $reservation->cancel_reason,
                $payment !== null ? "Your {$paid} is being refunded in full to the account you paid from." : null,
            ]));
        }

        return ["Your reservation for {$item} has been updated."];
    }
}
