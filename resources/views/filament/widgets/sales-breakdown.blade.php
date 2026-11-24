@php
    use App\Reports\StatementLine;

    $margin = fn (?float $percent) => $percent === null ? '—' : number_format($percent, 1).'%';
    $totalMinor = max(1, $total['revenue']->getMinorAmount()->toInt());
@endphp
<x-filament-widgets::widget>
    <x-filament::section data-tour="what-sold" heading="What sold" :description="$period.' · amounts in whole cedis · click a row for details'">
        <div class="yg-tables"><div class="yg-grid yg-grid--tables">
            @foreach ($tables as $table)
                <div class="yg-panel">
                    <table class="yg-table">
                        <caption>{{ $table['caption'] }}</caption>
                        <thead>
                            <tr>
                                <th scope="col"><span class="yg-sr-only">{{ \Illuminate\Support\Str::after($table['caption'], 'By ') }}</span></th>
                                <th scope="col">Items</th>
                                <th scope="col">Sales</th>
                                <th scope="col">Profit</th>
                                <th scope="col">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($table['rows'] as $row)
                                <tr
                                    class="yg-row-link"
                                    tabindex="0"
                                    role="button"
                                    aria-label="Details for {{ $row['label'] }}"
                                    wire:click="mountAction('details', {{ \Illuminate\Support\Js::from(['by' => $table['by'], 'label' => $row['label']]) }})"
                                    x-on:keydown.enter.prevent="$el.click()"
                                    x-on:keydown.space.prevent="$el.click()"
                                >
                                    <th scope="row">
                                        <span class="yg-table__label">{{ $row['label'] }}</span>
                                        {{-- Share of all sales in the period. --}}
                                        <span class="yg-share" aria-hidden="true"><span style="width: {{ round($row['revenue']->getMinorAmount()->toInt() / $totalMinor * 100, 1) }}%"></span></span>
                                    </th>
                                    <td>{{ $row['units'] }}</td>
                                    <td>{{ StatementLine::format($row['revenue'], true) }}</td>
                                    <td @class(['yg-neg' => $row['profit']->isNegative()])>{{ StatementLine::format($row['profit'], true) }}</td>
                                    <td @class(['yg-neg' => ($row['marginPercent'] ?? 0) < 0])>{{ $margin($row['marginPercent']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="yg-table__empty">No sales in this period</td></tr>
                            @endforelse
                        </tbody>
                        @if (count($table['rows']) > 0)
                            <tfoot>
                                <tr>
                                    <th scope="row">Total</th>
                                    <td>{{ $total['units'] }}</td>
                                    <td>{{ StatementLine::format($total['revenue'], true) }}</td>
                                    <td @class(['yg-neg' => $total['profit']->isNegative()])>{{ StatementLine::format($total['profit'], true) }}</td>
                                    <td @class(['yg-neg' => ($total['marginPercent'] ?? 0) < 0])>{{ $margin($total['marginPercent']) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            @endforeach
        </div></div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
