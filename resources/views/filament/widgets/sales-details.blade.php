{{-- The details behind one "What sold" row (App\Services\Reports\BuyingInsights::forGroup). --}}
@php
    use App\Reports\StatementLine;

    $cedis = fn ($money) => 'GH₵'.StatementLine::format($money, true);
    $tone = $insights['suggestion']['tone'];
@endphp
<div class="yg-details">
    <div @class(['yg-callout', 'is-'.$tone])>
        <x-filament::icon
            :icon="match ($tone) { 'buy' => 'heroicon-o-shopping-cart', 'careful' => 'heroicon-o-exclamation-triangle', default => 'heroicon-o-pause-circle' }"
            class="yg-callout__icon"
        />
        <div>
            <p class="yg-callout__title">{{ match ($tone) { 'buy' => 'Worth buying more', 'careful' => 'Buy carefully', default => 'Hold off for now' } }}</p>
            <p>{{ $insights['suggestion']['text'] }}</p>
        </div>
    </div>

    <dl class="yg-stats">
        <div class="yg-stat"><dt>Items sold</dt><dd>{{ $insights['units'] }}</dd></div>
        <div class="yg-stat"><dt>Sales</dt><dd>{{ $cedis($insights['revenue']) }}</dd></div>
        <div class="yg-stat">
            <dt>Profit</dt>
            <dd @class(['yg-neg' => $insights['profit']->isNegative()])>{{ $cedis($insights['profit']) }}</dd>
            @if ($insights['marginPercent'] !== null)
                <span class="yg-stat__note">{{ number_format($insights['marginPercent'], 1) }}% margin</span>
            @endif
        </div>
        <div class="yg-stat"><dt>Profit per item</dt><dd>{{ $insights['averageProfit'] ? $cedis($insights['averageProfit']) : '—' }}</dd></div>
        <div class="yg-stat"><dt>Days to sell</dt><dd>{{ $insights['averageDays'] ?? '—' }}</dd><span class="yg-stat__note">on average, from arrival</span></div>
        <div class="yg-stat"><dt>Selling per month</dt><dd>{{ rtrim(rtrim(number_format($insights['perMonth'], 1), '0'), '.') }}</dd></div>
        <div class="yg-stat"><dt>In store now</dt><dd>{{ $insights['inStore'] }}</dd></div>
        <div class="yg-stat"><dt>On the way</dt><dd>{{ $insights['onTheWay'] }}</dd><span class="yg-stat__note">bought, not yet arrived</span></div>
        <div class="yg-stat"><dt>On buying list</dt><dd>{{ $insights['onList'] }}</dd></div>
    </dl>

    @if (count($insights['bestSellers']) > 0)
        <div class="yg-panel">
            <table class="yg-table">
                <caption>Best sellers</caption>
                <thead>
                    <tr><th scope="col">Item</th><th scope="col">Sold</th><th scope="col">Profit</th><th scope="col">Days to sell</th><th scope="col">Paid in UK</th></tr>
                </thead>
                <tbody>
                    @foreach ($insights['bestSellers'] as $seller)
                        <tr>
                            <th scope="row">{{ $seller['product'] }}</th>
                            <td>{{ $seller['units'] }}</td>
                            <td @class(['yg-neg' => $seller['profit']->isNegative()])>{{ StatementLine::format($seller['profit'], true) }}</td>
                            <td>{{ $seller['averageDays'] }}</td>
                            <td>{{ $seller['averageUkCost']?->formatToLocale('en_GB') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (count($insights['recent']) > 0)
        <div class="yg-panel">
            <table class="yg-table">
                <caption>Latest sales</caption>
                <thead>
                    <tr><th scope="col">Item</th><th scope="col">Sold</th><th scope="col">Paid</th><th scope="col">Profit</th><th scope="col">Days</th></tr>
                </thead>
                <tbody>
                    @foreach ($insights['recent'] as $sale)
                        <tr>
                            <th scope="row">
                                <span class="yg-table__label">{{ $sale['product'] }}</span>
                                <span class="yg-table__sub">{{ $sale['condition'] }} · {{ $sale['reference'] }}</span>
                            </th>
                            <td>{{ $sale['date']->format('j M') }}</td>
                            <td>{{ StatementLine::format($sale['revenue'], true) }}</td>
                            <td @class(['yg-neg' => $sale['profit']->isNegative()])>{{ StatementLine::format($sale['profit'], true) }}</td>
                            <td>{{ $sale['days'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <p class="yg-details__foot">
        The suggestion aims for about {{ \App\Services\Reports\BuyingInsights::MONTHS_OF_COVER }} months of stock at the pace you're selling, counting what's in the store and on the way. "Paid in UK" is the average cost of the ones you sold.
    </p>
</div>
