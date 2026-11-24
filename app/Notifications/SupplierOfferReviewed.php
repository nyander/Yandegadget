<?php

namespace App\Notifications;

use App\Enums\OfferStatus;
use App\Models\SupplierOffer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the supplier whether the shop is buying their item.
 */
class SupplierOfferReviewed extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject($this->title())
            ->greeting("Hello {$notifiable->name},")
            ->line($this->body())
            ->action('View the offer', route('supplier.offers.show', $this->offer));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => route('supplier.offers.show', $this->offer),
            'supplier_offer_id' => $this->offer->id,
        ];
    }

    private function title(): string
    {
        return $this->offer->status === OfferStatus::Accepted
            ? "We'd like to buy {$this->offer->title} ({$this->offer->reference})"
            : "Your offer {$this->offer->reference} was declined";
    }

    private function body(): string
    {
        if ($this->offer->status === OfferStatus::Accepted) {
            $price = $this->offer->agreed_price?->formatToLocale('en_GB');

            return config('app.name')." will buy {$this->offer->title} for {$price}. We'll be in touch to arrange payment and collection.";
        }

        return "Thanks for offering {$this->offer->title}. We won't be buying it this time: {$this->offer->decline_reason}";
    }
}
