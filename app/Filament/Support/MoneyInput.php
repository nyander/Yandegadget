<?php

namespace App\Filament\Support;

use App\Services\Pricing;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/**
 * A decimal text input bound to a Money attribute (see App\Casts\MoneyCast).
 */
final class MoneyInput
{
    /**
     * @param  string|Closure(Get): string  $currency  A fixed currency, or a closure reading it from the form.
     */
    public static function make(string $name, string|Closure $currency): TextInput
    {
        $input = TextInput::make($name)
            ->numeric()
            ->minValue(0)
            ->step('0.01')
            ->inputMode('decimal')
            ->formatStateUsing(fn (mixed $state) => $state instanceof Money ? (string) $state->getAmount() : $state)
            // Money is stored as an amount and a currency column, so the form can't
            // load it by name: read it from the record when editing.
            ->afterStateHydrated(function (TextInput $component, mixed $state, mixed $record) use ($name): void {
                if (blank($state) && $record instanceof Model && $record->getAttribute($name) instanceof Money) {
                    $component->state((string) $record->getAttribute($name)->getAmount());
                }
            })
            ->dehydrateStateUsing(function (mixed $state, Get $get) use ($currency): ?Money {
                if (blank($state)) {
                    return null;
                }

                $code = is_string($currency) ? $currency : $currency($get);

                return Money::of((string) $state, $code, roundingMode: RoundingMode::HalfUp);
            });

        if (is_string($currency)) {
            $input->prefix(match ($currency) {
                'GBP' => '£',
                'GHS' => 'GH₵',
                default => $currency,
            });
        }

        return $input;
    }

    /**
     * Shows what an amount is in cedis at today's rate, under the box, as you type.
     * e.g. "≈ GH₵5,100.00 at today's rate (£1 = GH₵15.00, set 20 Nov)."
     */
    public static function withCedis(TextInput $input, string $currency, string $lead = ''): TextInput
    {
        $display = (string) config('shop.display_currency');

        if ($currency === $display) {
            return $input;
        }

        return $input
            ->live(debounce: 400)
            ->belowContent(function (mixed $state) use ($currency, $display, $lead): HtmlString {
                $pricing = app(Pricing::class);
                $rate = $pricing->rate($currency, $display);

                if ($rate === null) {
                    return new HtmlString('<span class="yg-rate is-missing">No '.e($currency).' to '.e($display).' rate yet. Add one under Settings → Exchange rates to see cedi prices.</span>');
                }

                $one = $pricing->convert(Money::of(1, $currency), $display);
                $rateText = Money::of(1, $currency)->formatToLocale('en_GB').' = '.$one?->formatToLocale('en_GH').', set '.$rate->effective_at->format('j M');

                if (! is_numeric($state) || (float) $state <= 0) {
                    return new HtmlString('<span class="yg-rate">'.e($lead).'Today\'s rate: '.e($rateText).'.</span>');
                }

                $cedis = $pricing->convert(Money::of((string) $state, $currency, roundingMode: RoundingMode::HalfUp), $display);

                return new HtmlString('<span class="yg-rate">'.e($lead).'≈ <strong>'.e((string) $cedis?->formatToLocale('en_GH')).'</strong> at today\'s rate ('.e($rateText).').</span>');
            });
    }
}
