<?php

namespace App\Filament\Widgets;

use App\Services\Reports\BuyingInsights;
use App\Services\Reports\SalesReport;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Filament\Widgets\Widget;

/** How long stock has been sitting. Each band opens the items in it. */
class StockAge extends Widget implements HasActions, HasSchemas
{
    use AuthorisesReports, InteractsWithActions, InteractsWithSchemas;

    /** Loaded with the page, so the dashboard tour can point at it. */
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected string $view = 'filament.widgets.stock-age';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return ['stock' => app(SalesReport::class)->stockAge()];
    }

    public function detailsAction(): Action
    {
        return Action::make('details')
            ->slideOver()
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalHeading(fn (array $arguments) => 'In stock: '.mb_lcfirst($this->band($arguments)['title']))
            ->modalDescription('Items in store or reserved, oldest first. Amounts in cedis.')
            ->modalContent(fn (array $arguments) => view('filament.widgets.stock-age-details', [
                'band' => $this->band($arguments),
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function band(array $arguments): array
    {
        return app(BuyingInsights::class)->forAgeBand((int) ($arguments['band'] ?? 0));
    }
}
