@php
    use App\Reports\StatementLine;

    $margin = fn (?float $percent) => $percent === null ? '—' : number_format($percent, 1).'%';
    $totalMinor = max(1, $total['revenue']->getMinorAmount()->toInt());
@endphp
<x-filament-widgets::widget>
    <x-filament::section data-tour="what-sold" heading="What sold" :description="$period.' · amounts in whole cedis'">
        <div class="yg-tables"><div class="yg-grid yg-grid--tables">
            @foreach ($tables as $caption => $rows)
                <div class="yg-panel">
                    <table class="yg-table">
                        <caption>{{ $caption }}</caption>
                        <thead>
                            <tr>
                                <th scope="col"><span class="yg-sr-only">{{ \Illuminate\Support\Str::after($caption, 'By ') }}</span></th>
                                <th scope="col">Items</th>
                                <th scope="col">Sales</th>
                                <th scope="col">Profit</th>
                                <th scope="col">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
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
                        @if (count($rows) > 0)
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
</x-filament-widgets::widget>
