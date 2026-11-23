@php use App\Reports\StatementLine; @endphp
<x-filament-widgets::widget>
    <x-filament::section data-tour="stock-age" heading="Stock age" description="Items in store or reserved, by days since they arrived. Amounts at cost, in whole cedis.">
        <div class="yg-panel">
            <table class="yg-table">
                <thead>
                    <tr><th scope="col">Age</th><th scope="col">Items</th><th scope="col">Cost</th></tr>
                </thead>
                <tbody>
                    @foreach ($stock['buckets'] as $bucket)
                        <tr>
                            <th scope="row">{{ $bucket['label'] }}</th>
                            <td>{{ $bucket['units'] }}</td>
                            <td>{{ StatementLine::format($bucket['value'], true) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="row">Total</th>
                        <td>{{ $stock['units'] }}</td>
                        <td>{{ StatementLine::format($stock['value'], true) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
