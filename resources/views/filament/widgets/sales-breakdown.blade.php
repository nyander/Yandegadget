@php use App\Reports\StatementLine; @endphp
<x-filament-widgets::widget>
    <x-filament::section data-tour="what-sold" heading="What sold" :description="$period.' · amounts in cedis'">
        <div class="yg-grid">
            @foreach ($tables as $caption => $rows)
                <table class="yg-table">
                    <caption>{{ $caption }}</caption>
                    <thead>
                        <tr><th scope="col"></th><th scope="col">Items</th><th scope="col">Sales</th><th scope="col">Profit</th><th scope="col">Margin</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ $row['units'] }}</td>
                                <td>{{ StatementLine::format($row['revenue']) }}</td>
                                <td @class(['yg-neg' => $row['profit']->isNegative()])>{{ StatementLine::format($row['profit']) }}</td>
                                <td @class(['yg-neg' => ($row['marginPercent'] ?? 0) < 0])>{{ $row['marginPercent'] === null ? '—' : number_format($row['marginPercent'], 1).'%' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="yg-table__empty">No sales in this period</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
