<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * "Something in this area changed": pages showing that area refresh straight
 * away. Carries only the area's name, never the record itself, so nothing
 * private travels over the websocket; pages fetch fresh data as usual.
 */
class RecordsChanged implements ShouldBroadcastNow
{
    use Dispatchable;

    /**
     * @param  list<string>  $channels  Private channel names (see routes/channels.php).
     */
    public function __construct(public string $topic, public array $channels) {}

    /**
     * @return list<PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return array_map(fn (string $name) => new PrivateChannel($name), $this->channels);
    }

    public function broadcastAs(): string
    {
        return 'records.changed';
    }

    /**
     * @return array{topic: string}
     */
    public function broadcastWith(): array
    {
        return ['topic' => $this->topic];
    }
}
