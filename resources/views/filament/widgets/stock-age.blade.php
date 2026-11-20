@php use App\Reports\StatementLine; @endphp
<x-filament-widgets::widget>
    <x-filament::section heading="Stock age" description="Items in store or reserved, by days since they arrived. Amounts at cost, in cedis.">
        <table class="yg-table">
            <thead>
                <tr><th scope="col">Age</th><th scope="col">Items</th><th scope="col">Cost</th></tr>
            </thead>
            <tbody>
                @foreach ($stock['buckets'] as $bucket)
                    <tr>
                        <td>{{ $bucket['label'] }}</td>
                        <td>{{ $bucket['units'] }}</td>
                        <td>{{ StatementLine::format($bucket['value']) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong>{{ $stock['units'] }}</strong></td>
                    <td><strong>{{ StatementLine::format($stock['value']) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </x-filament::section>
</x-filament-widgets::widget>
