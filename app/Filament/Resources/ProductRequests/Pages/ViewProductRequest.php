<?php

namespace App\Filament\Resources\ProductRequests\Pages;

use App\Filament\Resources\ProductRequests\ProductRequestResource;
use App\Models\ProductRequest;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\View\View;

/**
 * One customer request: what they want, who they are, the deposit, the item
 * found for them and what's happened so far, with the next step as buttons.
 */
class ViewProductRequest extends ViewRecord
{
    protected static string $resource = ProductRequestResource::class;

    public function getTitle(): string
    {
        /** @var ProductRequest $request */
        $request = $this->getRecord();

        return "{$request->reference} · {$request->description}";
    }

    /** Status, matched item and dates update while it's open. */
    public function getFooter(): ?View
    {
        return view('filament.live-refresh', ['every' => '10s']);
    }

    protected function getHeaderActions(): array
    {
        return [
            ProductRequestResource::matchAction()->button(),
            ProductRequestResource::declineAction()->button(),
            ProductRequestResource::cancelAction()->button()->outlined(),
        ];
    }
}
