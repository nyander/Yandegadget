<?php

namespace App\Filament\Resources\StockBatches\Pages;

use App\Filament\Resources\StockBatches\StockBatchResource;
use App\Models\StockBatch;
use App\Services\StockIntake;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * @property StockBatch $record
 */
class EditStockBatch extends EditRecord
{
    protected static string $resource = StockBatchResource::class;

    public function getTitle(): string
    {
        return "{$this->record->reference} · {$this->record->product->name} ×{$this->record->quantity}";
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var StockBatch $record */
        $updated = app(StockIntake::class)->updateBatch($record, $data);

        Notification::make()->success()->title("{$updated} ".str('item')->plural($updated).' updated')->send();

        return $record;
    }

    protected function getSavedNotification(): ?Notification
    {
        return null;
    }
}
