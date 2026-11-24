<?php

namespace App\Filament\Widgets;

use App\Enums\LocationType;
use App\Filament\Resources\BuyingListItems\BuyingListItemResource;
use App\Filament\Support\ReportPeriod;
use App\Models\BuyingListItem;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Location;
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
 * "What sold" by category, condition or store (one table at a time), with
 * filters and sorting. Each row opens the sales behind it, with a buying
 * suggestion and "Add to buying list".
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

    public const GROUPS = ['category' => 'Category', 'condition' => 'Condition', 'location' => 'Store'];

    public const SORTS = ['revenue' => 'Sales', 'profit' => 'Profit', 'margin' => 'Margin', 'units' => 'Items sold', 'label' => 'Name'];

    /** Which table is showing: category, condition or location. */
    public string $groupBy = 'category';

    /** Empty means the usual order: conditions by grade, the rest by sales. */
    public string $sortBy = '';

    public string $sortDirection = 'desc';

    public string $filterCategory = '';

    public string $filterCondition = '';

    public string $filterLocation = '';

    public function showGroup(string $by): void
    {
        $this->groupBy = array_key_exists($by, self::GROUPS) ? $by : 'category';
        $this->sortBy = '';
    }

    /** Header click: sort by that column, or flip the direction if it already is. */
    public function sortTable(string $column): void
    {
        if (! array_key_exists($column, self::SORTS) && $column !== 'grade') {
            return;
        }

        if ($this->sortBy === $column) {
            $this->flipDirection();

            return;
        }

        $this->sortBy = $column;
        $this->sortDirection = in_array($column, ['label', 'grade'], true) ? 'asc' : 'desc';
    }

    public function updatedSortBy(): void
    {
        $this->sortDirection = in_array($this->sortBy, ['label', 'grade'], true) ? 'asc' : 'desc';
    }

    public function flipDirection(): void
    {
        [$sort, $direction] = $this->effectiveSort();
        $this->sortBy = $sort;
        $this->sortDirection = $direction === 'asc' ? 'desc' : 'asc';
    }

    /**
     * The sort actually used, filling in the usual order when none is chosen.
     *
     * @return array{0: 'label'|'units'|'revenue'|'profit'|'margin'|'grade', 1: 'asc'|'desc'}
     */
    private function effectiveSort(): array
    {
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return match (true) {
            $this->sortBy === 'grade' && $this->group() === 'condition' => ['grade', $direction],
            array_key_exists($this->sortBy, self::SORTS) => [$this->sortBy, $direction],
            $this->group() === 'condition' => ['grade', 'asc'],
            default => ['revenue', 'desc'],
        };
    }

    public function clearFilters(): void
    {
        $this->filterCategory = $this->filterCondition = $this->filterLocation = '';
    }

    /**
     * Filters in use, leaving out the one the table is grouped by.
     *
     * @return array<string, string>
     */
    public function activeFilters(): array
    {
        return array_filter([
            'category' => $this->filterCategory,
            'condition' => $this->filterCondition,
            'location' => $this->filterLocation,
        ], fn (string $value, string $by) => $value !== '' && $by !== $this->group(), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $report = app(SalesReport::class);
        [$from, $to] = ReportPeriod::from($this->pageFilters);
        $only = $this->activeFilters();
        [$sort, $direction] = $this->effectiveSort();

        return [
            'group' => $this->group(),
            'rows' => $report->breakdown($from, $to, $this->group(), $only, $sort, $direction),
            'total' => $report->summary($from, $to, $only),
            'sort' => $sort,
            'direction' => $direction,
            'sortOptions' => $this->group() === 'condition' ? ['grade' => 'Grade', ...self::SORTS] : self::SORTS,
            'filters' => [
                'category' => ['label' => 'Category', 'all' => 'All categories', 'model' => 'filterCategory', 'options' => Category::query()->orderBy('name')->pluck('name')->all()],
                'condition' => ['label' => 'Condition', 'all' => 'All conditions', 'model' => 'filterCondition', 'options' => Condition::query()->orderBy('sort_order')->pluck('name')->all()],
                'location' => ['label' => 'Store', 'all' => 'All stores', 'model' => 'filterLocation', 'options' => Location::query()->where('type', LocationType::Store)->orderBy('name')->pluck('name')->all()],
            ],
            'only' => $only,
            'period' => $from->format('j M').' – '.$to->format('j M Y'),
        ];
    }

    /** @return 'category'|'condition'|'location' */
    private function group(): string
    {
        return match ($this->groupBy) {
            'condition' => 'condition',
            'location' => 'location',
            default => 'category',
        };
    }

    public function detailsAction(): Action
    {
        return Action::make('details')
            ->slideOver()
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalHeading(fn (array $arguments) => $this->title($arguments))
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
            ->extraModalWindowAttributes(['data-tour-panel' => 'add-to-buying-list'])
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
                'source' => 'What sold · '.$this->title($arguments),
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

        return app(BuyingInsights::class)->forGroup($from, $to, $this->groupBy($arguments), $this->label($arguments), $this->filtersFor($arguments));
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
     * The row's label, plus any filters, e.g. "Mint · Phones".
     *
     * @param  array<string, mixed>  $arguments
     */
    private function title(array $arguments): string
    {
        return implode(' · ', [$this->label($arguments), ...array_values($this->filtersFor($arguments))]);
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function label(array $arguments): string
    {
        return (string) ($arguments['label'] ?? '');
    }

    /**
     * Filters in use, minus the row's own group.
     *
     * @param  array<string, mixed>  $arguments
     * @return array<string, string>
     */
    private function filtersFor(array $arguments): array
    {
        return array_diff_key($this->activeFilters(), [$this->groupBy($arguments) => true]);
    }
}
