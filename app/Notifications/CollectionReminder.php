<?php

namespace App\Notifications;

use App\Models\ProductRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A nudge a few days before a reserved item goes back on sale
 * (Shop settings → "Remind customers").
 */
class CollectionReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ProductRequest $request,
        public CarbonImmutable $collectBy,
        public int $feePercent,
    ) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $request = $this->request->loadMissing('stockUnit');

        $mail = (new MailMessage)
            ->subject($this->title())
            ->greeting("Hello {$notifiable->name},")
            ->line($this->body());

        if ($request->stockUnit) {
            $mail->line("Quote reference {$request->stockUnit->reference} at the store: 27 Ayawaso, Legon Boundary, Accra.");
        }

        return $mail
            ->line('If you no longer want it, you can cancel from your request page.')
            ->action('View your request', route('requests.show', $request));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => route('requests.show', $this->request),
            'product_request_id' => $this->request->id,
            'status' => $this->request->status->value,
        ];
    }

    private function title(): string
    {
        return "Reminder: please collect your item by {$this->collectBy->format('l j F')} ({$this->request->reference})";
    }

    private function body(): string
    {
        $after = $this->feePercent > 0
            ? "After that it goes back on sale and {$this->feePercent}% of your deposit is kept."
            : 'After that it goes back on sale.';

        return "\"{$this->request->description}\" is still reserved for you in the store until {$this->collectBy->format('l j F')}. {$after}";
    }
}
