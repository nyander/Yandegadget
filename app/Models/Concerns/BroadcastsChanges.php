<?php

namespace App\Models\Concerns;

use App\Support\LiveUpdates;

/**
 * Saving or deleting the model tells open pages to refresh (App\Support\LiveUpdates).
 * Models say which area they belong to and who should hear about it.
 */
trait BroadcastsChanges
{
    public static function bootBroadcastsChanges(): void
    {
        $notify = function (self $model): void {
            app(LiveUpdates::class)->changed($model->liveTopic(), $model->liveChannels());
        };

        static::saved($notify);
        static::deleted($notify);
    }

    /** The area of the app this record shows up in, e.g. "buying-list". */
    abstract public function liveTopic(): string;

    /**
     * Who should hear about it. Back office by default.
     *
     * @return list<string>
     */
    public function liveChannels(): array
    {
        return [LiveUpdates::BACKOFFICE];
    }
}
