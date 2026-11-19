<?php

namespace App\Notifications;

use App\Enums\RequestStatus;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Keeps the customer up to date as their request moves along ("ProductAcquired" in 2020).
 */
class ProductRequestUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    private RequestStatus $status;

    public function __construct(public ProductRequest $request)
    {
        // Remember the status at the time, in case it changes before the queue runs.
        $this->status = $request->status;
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
        $request = $this->request->loadMissing('stockUnit');

        $mail = (new MailMessage)
            ->subject($this->title())
            ->greeting("Hello {$notifiable->name},")
            ->line($this->body());

        if ($this->status === RequestStatus::ReadyForCollection && $request->stockUnit) {
            $mail->line("Quote reference {$request->stockUnit->reference} at the store: 27 Ayawaso, Legon Boundary, Accra.");
        }

        return $mail->action('View your request', route('requests.show', $request));
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
            'status' => $this->status->value,
        ];
    }

    private function title(): string
    {
        $ref = $this->request->reference;

        return match ($this->status) {
            RequestStatus::Open => "We've received your deposit ({$ref})",
            RequestStatus::Sourced => "We've found your item ({$ref})",
            RequestStatus::ReadyForCollection => "Your item is ready in store ({$ref})",
            RequestStatus::Cancelled => "Your request {$ref} has been cancelled",
            default => "Update on your request {$ref}",
        };
    }

    private function body(): string
    {
        $what = $this->request->description;

        return match ($this->status) {
            RequestStatus::Open => "Thanks. We'll look for \"{$what}\" and let you know when we find one.",
            RequestStatus::Sourced => "We've found \"{$what}\" in the UK and it's on its way to Accra. We'll tell you when it arrives.",
            RequestStatus::ReadyForCollection => "\"{$what}\" is in the store and reserved for you. Your deposit counts towards the price.",
            RequestStatus::Cancelled => trim("Your request for \"{$what}\" has been cancelled. ".($this->request->paidDeposit()?->refunded_at ? 'Your deposit is being refunded.' : '')),
            default => "Your request for \"{$what}\" has been updated.",
        };
    }
}
