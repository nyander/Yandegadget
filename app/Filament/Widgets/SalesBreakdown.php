<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BuyingListItems\BuyingListItemResource;
use App\Filament\Support\ReportPeriod;
use App\Models\BuyingListItem;
use App\Services\Reports\BuyingInsights;
use App\Services\Reports\SalesReport;
use Brick\Money\Money;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

/**
 * "What sold" by category, condition and store. Each row opens the sales
 * behind it, with a buying suggestion and "Add to buying list".
 */
class SalesBreakdown extends Widget implements HasActions, HasSchemas
{
    use AuthorisesReports, InteractsWithActions, InteractsWithPageFilters, InteractsWithSchemas {
        // The page-filters version only aliases the old $this->filters name, which isn't used here.
        InteractsWithSchemas::__get insteadof InteractsWithPageFilters;
    }

    /** Loaded with the page, so the dashboard tour can point at it. */
    protected static bool $isLazy = false;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.sales-breakdown';

    public const GROUPS = ['category' => 'By category', 'condition' => 'By condition', 'location' => 'By store'];

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $report = app(SalesReport::class);
        [$from, $to] = ReportPeriod::from($this->pageFilters);

        $tables = [];
        foreach (self::GROUPS as $by => $caption) {
            $tables[] = ['by' => $by, 'caption' => $caption, 'rows' => $report->breakdown($from, $to, $by)];
        }

        return [
            'total' => $report->summary($from, $to),
            'tables' => $tables,
            'period' => $from->format('j M').' – '.$to->format('j M Y'),
        ];
    }

    public function detailsAction(): Action
    {
        return Action::make('details')
            ->slideOver()
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalHeading(fn (array $arguments) => $this->label($arguments))
            ->modalDescription(function (): string {
                [$from, $to] = ReportPeriod::from($this->pageFilters);

                return 'Sales '.$from->format('j M').' – '.$to->format('j M Y').', amounts in cedis';
            })
            ->modalContent(fn (array $arguments) => view('filament.widgets.sales-details', [
                'insights' => $this->insights($arguments),
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->extraModalFooterActions(fn (array $arguments) => [
                $this->addToBuyingListAction($arguments),
            ]);
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function addToBuyingListAction(array $arguments): CreateAction
    {
        return CreateAction::make('addToBuyingList')
            ->label('Add to buying list')
            ->icon(Heroicon::OutlinedClipboardDocumentList)
            ->model(BuyingListItem::class)
            ->modalHeading('Add to buying list')
            ->modalDescription('For your next UK buying trip. You can change everything before saving.')
            ->modalSubmitActionLabel('Add to list')
            ->createAnother(false)
            ->schema(BuyingListItemResource::fields())
            ->fillForm(function () use ($arguments): array {
                $prefill = $this->insights($arguments)['prefill'];

                return [
                    ...$prefill,
                    'target_cost' => $prefill['target_cost'] instanceof Money ? (string) $prefill['target_cost']->getAmount() : null,
                ];
            })
            ->mutateDataUsing(fn (array $data) => [
                ...$data,
                'added_by' => auth()->id(),
                'source' => 'What sold · '.$this->label($arguments),
            ])
            ->successNotificationTitle('Added to the buying list');
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function insights(array $arguments): array
    {
        [$from, $to] = ReportPeriod::from($this->pageFilters);

        return app(BuyingInsights::class)->forGroup($from, $to, $this->groupBy($arguments), (string) ($arguments['label'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return 'category'|'condition'|'location'
     */
    private function groupBy(array $arguments): string
    {
        return match ($arguments['by'] ?? null) {
            'condition' => 'condition',
            'location' => 'location',
            default => 'category',
        };
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function label(array $arguments): string
    {
        return (string) ($arguments['label'] ?? '');
    }
}
