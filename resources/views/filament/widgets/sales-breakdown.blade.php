@php
    use App\Filament\Widgets\SalesBreakdown;
    use App\Reports\StatementLine;
    use Illuminate\Support\Js;

    $margin = fn (?float $percent) => $percent === null ? '—' : number_format($percent, 1).'%';
    $totalMinor = max(1, $total['revenue']->getMinorAmount()->toInt());
    $columns = [
        'units' => 'Items',
        'revenue' => 'Sales',
        'share' => 'Share of sales',
        'profit' => 'Profit',
        'each' => 'Profit each',
        'margin' => 'Margin',
    ];
    $sortable = ['label', 'units', 'revenue', 'profit', 'margin'];
    $groupName = SalesBreakdown::GROUPS[$group];
@endphp
<x-filament-widgets::widget>
    <x-filament::section data-tour="what-sold" :heading="app(\App\Support\ReportGlossary::class)->label('What sold', 'dashboard:what-sold')" :description="$period.' · amounts in whole cedis · click a row for details'">
        <div class="yg-toolbar" data-tour="what-sold-tools">
            <x-filament::tabs label="Show sales by" contained class="yg-toolbar__tabs">
                @foreach (SalesBreakdown::GROUPS as $by => $caption)
                    <x-filament::tabs.item :active="$group === $by" wire:click="showGroup('{{ $by }}')">
                        By {{ mb_strtolower($caption) }}
                    </x-filament::tabs.item>
                @endforeach
            </x-filament::tabs>

            <div class="yg-toolbar__fields">
                @foreach ($filters as $by => $filter)
                    @continue($by === $group)
                    <label class="yg-field">
                        <span class="yg-field__label">{{ $filter['label'] }}</span>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="{{ $filter['model'] }}">
                                <option value="">{{ $filter['all'] }}</option>
                                @foreach ($filter['options'] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </label>
                @endforeach

                <label class="yg-field">
                    <span class="yg-field__label">Sort by</span>
                    <div class="yg-field__row">
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="sortBy">
                                @foreach ($sortOptions as $value => $name)
                                    <option value="{{ $value }}" @selected($sort === $value)>{{ $name }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                        <x-filament::icon-button
                            color="gray"
                            :icon="$direction === 'asc' ? \Filament\Support\Icons\Heroicon::BarsArrowUp : \Filament\Support\Icons\Heroicon::BarsArrowDown"
                            :label="$direction === 'asc' ? 'Lowest first. Click for highest first' : 'Highest first. Click for lowest first'"
                            :tooltip="$direction === 'asc' ? 'Lowest first' : 'Highest first'"
                            wire:click="flipDirection"
                        />
                    </div>
                </label>
            </div>
        </div>

        @if ($only !== [])
            <p class="yg-filters-note">
                Showing only {{ implode(' · ', $only) }}.
                <button type="button" class="yg-table__link" wire:click="clearFilters">Show everything</button>
            </p>
        @endif

        <div class="yg-panel" wire:loading.class="yg-is-loading">
            <table class="yg-table yg-table--wide">
                <caption class="yg-sr-only">Sales by {{ mb_strtolower($groupName) }}</caption>
                <thead>
                    <tr>
                        @foreach (['label' => $groupName, ...$columns] as $key => $heading)
                            <th scope="col" @if (in_array($key, $sortable, true)) aria-sort="{{ $sort === $key ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}" @endif>
                                @if (in_array($key, $sortable, true))
                                    <button type="button" @class(['yg-sort', 'is-active' => $sort === $key]) wire:click="sortTable('{{ $key }}')">
                                        {{ $heading }}
                                        <x-filament::icon
                                            class="yg-sort__arrow"
                                            :icon="$sort === $key ? ($direction === 'asc' ? 'heroicon-m-arrow-up' : 'heroicon-m-arrow-down') : 'heroicon-m-chevron-up-down'"
                                        />
                                    </button>
                                @else
                                    {{ $heading }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php $share = round($row['revenue']->getMinorAmount()->toInt() / $totalMinor * 100, 1); @endphp
                        <tr
                            wire:key="{{ $group }}-{{ $row['label'] }}"
                            class="yg-row-link"
                            tabindex="0"
                            role="button"
                            aria-label="Details for {{ $row['label'] }}"
                            wire:click="mountAction('details', {{ Js::from(['by' => $group, 'label' => $row['label']]) }})"
                            x-on:keydown.enter.prevent="$el.click()"
                            x-on:keydown.space.prevent="$el.click()"
                        >
                            <th scope="row"><span class="yg-table__label">{{ $row['label'] }}</span></th>
                            <td>{{ $row['units'] }}</td>
                            <td>{{ StatementLine::format($row['revenue'], true) }}</td>
                            <td>
                                <span class="yg-share-cell">
                                    <span class="yg-share" aria-hidden="true"><span style="width: {{ max(0, $share) }}%"></span></span>
                                    {{ number_format($share, 0) }}%
                                </span>
                            </td>
                            <td @class(['yg-neg' => $row['profit']->isNegative()])>{{ StatementLine::format($row['profit'], true) }}</td>
                            <td @class(['yg-neg' => $row['profit']->isNegative()])>{{ StatementLine::format($row['profit']->dividedBy(max(1, $row['units']), \Brick\Math\RoundingMode::HalfUp), true) }}</td>
                            <td @class(['yg-neg' => ($row['marginPercent'] ?? 0) < 0])>{{ $margin($row['marginPercent']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="yg-table__empty">
                                {{ $only === [] ? 'No sales in this period' : 'No sales match these filters' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if (count($rows) > 0)
                    <tfoot>
                        <tr>
                            <th scope="row">Total</th>
                            <td>{{ $total['units'] }}</td>
                            <td>{{ StatementLine::format($total['revenue'], true) }}</td>
                            <td>100%</td>
                            <td @class(['yg-neg' => $total['profit']->isNegative()])>{{ StatementLine::format($total['profit'], true) }}</td>
                            <td @class(['yg-neg' => $total['profit']->isNegative()])>{{ StatementLine::format($total['profit']->dividedBy(max(1, $total['units']), \Brick\Math\RoundingMode::HalfUp), true) }}</td>
                            <td @class(['yg-neg' => ($total['marginPercent'] ?? 0) < 0])>{{ $margin($total['marginPercent']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
