@php use App\Reports\StatementLine; @endphp
<x-filament-widgets::widget wire:poll.30s>
    <x-filament::section data-tour="stock-age" :heading="app(\App\Support\ReportGlossary::class)->label('Stock age', 'dashboard:stock-age')" description="Items in store or reserved, by days since they arrived. Amounts at cost, in whole cedis. Click a row to see the items.">
        <div class="yg-panel">
            <table class="yg-table">
                <thead>
                    <tr><th scope="col">Age</th><th scope="col">Items</th><th scope="col">Cost</th></tr>
                </thead>
                <tbody>
                    @foreach ($stock['buckets'] as $index => $bucket)
                        <tr
                            @if ($bucket['units'] > 0)
                                class="yg-row-link"
                                tabindex="0"
                                role="button"
                                aria-label="Items in stock {{ mb_lcfirst($bucket['label']) }}"
                                wire:click="mountAction('details', {{ \Illuminate\Support\Js::from(['band' => $index]) }})"
                                x-on:keydown.enter.prevent="$el.click()"
                                x-on:keydown.space.prevent="$el.click()"
                            @endif
                        >
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

    <x-filament-actions::modals />
</x-filament-widgets::widget>
