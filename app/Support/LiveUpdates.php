<?php

namespace App\Support;

use App\Events\RecordsChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Tells open pages that something changed, over Reverb (Laravel's websocket
 * server), once the database change is committed. One signal per area and
 * channel per request, so adding 50 items sends one, not 50. If Reverb is
 * off or unreachable, saving still works and pages keep checking by themselves.
 */
class LiveUpdates
{
    /** Admins and anyone else with back-office access. */
    public const BACKOFFICE = 'backoffice';

    /** Store staff (check-in and selling). */
    public const STORE = 'store';

    /** @var array<string, true> */
    private array $sent = [];

    public static function userChannel(int $userId): string
    {
        return "App.Models.User.{$userId}";
    }

    public static function enabled(): bool
    {
        return config('broadcasting.default') === 'reverb';
    }

    /**
     * Where browsers connect. Public values only: the app key, never the secret.
     *
     * @return array{key: string, host: string, port: int, scheme: string}
     */
    public static function clientConfig(): array
    {
        $scheme = (string) config('broadcasting.connections.reverb.options.scheme', 'https');

        return [
            'key' => (string) config('broadcasting.connections.reverb.key'),
            'host' => (string) config('broadcasting.connections.reverb.options.host'),
            'port' => (int) config('broadcasting.connections.reverb.options.port', $scheme === 'https' ? 443 : 80),
            'scheme' => $scheme,
        ];
    }

    /**
     * @param  list<string>  $channels
     */
    public function changed(string $topic, array $channels): void
    {
        if (! self::enabled() || $channels === []) {
            return;
        }

        DB::afterCommit(function () use ($topic, $channels): void {
            $key = $topic.'|'.implode(',', $channels);

            if (isset($this->sent[$key])) {
                return;
            }

            $this->sent[$key] = true;

            try {
                broadcast(new RecordsChanged($topic, $channels));
            } catch (Throwable $e) {
                Log::warning('Live update not sent (is Reverb running?): '.$e->getMessage());
            }
        });
    }
}
