<?php

namespace App\Notifications;

use App\Filament\Resources\ProductRequests\ProductRequestResource;
use App\Models\ProductRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the shop a customer has paid a deposit for an item ("NewRequestedProduct" in 2020).
 */
class NewProductRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProductRequest $request) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $request = $this->request->loadMissing(['condition', 'category', 'customer']);

        return (new MailMessage)
            ->subject("New request {$request->reference}: {$request->description}")
            ->line("{$request->customer->name} has paid a deposit for: {$request->description}.")
            ->line("Category: {$request->category->name}. Condition: {$request->condition->grade} – {$request->condition->name}.")
            ->action('Open the request', $this->url());
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        return [
            ...FilamentNotification::make()
                ->title("New request {$this->request->reference}")
                ->body($this->request->description)
                ->info()
                ->actions([Action::make('view')->label('Open')->url($this->url())])
                ->getDatabaseMessage(),
            'url' => $this->url(),
            'product_request_id' => $this->request->id,
        ];
    }

    private function url(): string
    {
        return ProductRequestResource::getUrl('view', ['record' => $this->request], panel: 'admin');
    }
}
