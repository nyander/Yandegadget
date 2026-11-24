{{-- The items in one "Stock age" band (App\Services\Reports\BuyingInsights::forAgeBand). --}}
@php
    use App\Filament\Resources\StockUnits\StockUnitResource;
    use App\Reports\StatementLine;
@endphp
<div class="yg-details" data-tour-panel="stock-age-details">
    @if ($band['advice'])
        <div @class(['yg-callout', 'is-hold' => str_starts_with($band['advice'], 'Recently'), 'is-careful' => ! str_starts_with($band['advice'], 'Recently')]) data-tour="panel-advice">
            <x-filament::icon icon="heroicon-o-light-bulb" class="yg-callout__icon" />
            <p>{{ $band['advice'] }}</p>
        </div>
    @endif

    <dl class="yg-stats">
        <div class="yg-stat"><dt>Items</dt><dd>{{ $band['units'] }}</dd></div>
        <div class="yg-stat"><dt>Cost</dt><dd>GH₵{{ StatementLine::format($band['value'], true) }}</dd></div>
        <div class="yg-stat">
            <dt>Most of them</dt>
            <dd class="yg-stat__text">{{ array_key_first($band['byCategory']) ?? '—' }}</dd>
            @if ($band['byCategory'] !== [])
                <span class="yg-stat__note">{{ collect($band['byCategory'])->map(fn ($n, $c) => "{$c} {$n}")->join(' · ') }}</span>
            @endif
        </div>
    </dl>

    @if (count($band['items']) > 0)
        <div class="yg-panel" data-tour="panel-items">
            <table class="yg-table">
                <thead>
                    <tr><th scope="col">Item</th><th scope="col">Days</th><th scope="col">Cost</th><th scope="col">Listed at</th></tr>
                </thead>
                <tbody>
                    @foreach ($band['items'] as $item)
                        <tr>
                            <th scope="row">
                                <a class="yg-table__link" href="{{ StockUnitResource::getUrl('edit', ['record' => $item['id']]) }}">{{ $item['product'] }}</a>
                                <span class="yg-table__sub">{{ $item['condition'] }} · {{ $item['reference'] }} · {{ $item['location'] }}@if ($item['status'] === \App\Enums\StockStatus::Reserved) · reserved @endif</span>
                            </th>
                            <td>{{ $item['days'] }}</td>
                            <td>{{ StatementLine::format($item['cost'], true) }}</td>
                            <td>{{ StatementLine::format($item['price'], true) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="yg-details__foot">No items in this band.</p>
    @endif
</div>
