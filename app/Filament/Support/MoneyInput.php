<?php

namespace App\Filament\Support;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

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
}
