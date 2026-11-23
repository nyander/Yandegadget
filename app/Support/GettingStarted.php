<?php

namespace App\Support;

use App\Enums\Role;
use App\Enums\TransactionCategory;
use App\Models\ExchangeRate;
use App\Models\LedgerEntry;
use App\Models\StaffWage;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\ShopSettings;
use Illuminate\Support\Facades\DB;

/**
 * The owner's setup checklist on the dashboard. Most steps tick themselves
 * off from the data; the rest the owner marks done.
 */
class GettingStarted
{
    public function __construct(private readonly ShopSettings $settings) {}

    /**
     * @return list<array{key: string, title: string, text: string, url: string, help: string, done: bool, manual: bool}>
     */
    public function steps(User $user): array
    {
        $ticked = $this->ticked();

        $steps = [
            ['rate', 'Set today\'s exchange rate', 'Every cedi price on the website comes from it.', '/admin/exchange-rates', 'prices', ExchangeRate::exists()],
            ['deposits', 'Check the deposit for each condition', 'They\'re still the 2020 amounts.', '/admin/conditions', 'requests', null],
            ['refunds', 'Check the refund rules', 'The cancellation fee and days to collect a reserved item.', '/admin/shop-settings', 'settings', null],
            ['staff', 'Add your staff', 'They get an email to set their own password.', '/admin/users', 'people', User::role(Role::Staff->value)->exists()],
            ['security', 'Turn on two-factor sign-in for your account', 'Your account can see all the money.', '/settings/security', 'people', $this->hasTwoFactor($user)],
            ['stock', 'Add the stock already in the store', 'With the starting status "In store", and a photo.', '/admin/products', 'stock', StockUnit::exists()],
            ['opening', 'Enter your opening balances', 'Cash in the bank, money put into the business, loans.', '/admin/ledger-entries', 'money', $this->hasOpeningBalances()],
            ['wages', 'Add staff wages', 'Per day, week or month.', '/admin/staff-wages', 'money', StaffWage::exists()],
        ];

        return array_values(array_map(fn (array $step) => [
            'key' => $step[0],
            'title' => $step[1],
            'text' => $step[2],
            'url' => url($step[3]),
            'help' => app(HelpCentre::class)->url($step[4]),
            'done' => $step[5] ?? in_array($step[0], $ticked, true),
            'manual' => $step[5] === null,
        ], $steps));
    }

    public function markDone(string $key): void
    {
        $this->settings->set(ShopSettings::ONBOARDING_DONE, array_values(array_unique([...$this->ticked(), $key])));
    }

    public function dismiss(): void
    {
        $this->settings->set(ShopSettings::ONBOARDING_DISMISSED, true);
    }

    public function isDismissed(): bool
    {
        return (bool) $this->settings->get(ShopSettings::ONBOARDING_DISMISSED);
    }

    /**
     * @return list<string>
     */
    private function ticked(): array
    {
        $value = $this->settings->get(ShopSettings::ONBOARDING_DONE);

        return is_array($value) ? array_values(array_filter($value, 'is_string')) : [];
    }

    private function hasTwoFactor(User $user): bool
    {
        return $user->getAttribute('two_factor_confirmed_at') !== null
            || DB::table('passkeys')->where('user_id', $user->id)->exists();
    }

    private function hasOpeningBalances(): bool
    {
        $balanceSheet = array_map(
            fn (TransactionCategory $category) => $category->value,
            array_filter(TransactionCategory::cases(), fn (TransactionCategory $category) => $category->isBalanceSheet()),
        );

        return LedgerEntry::query()
            ->whereHas('transactionType', fn ($query) => $query->whereIn('category', $balanceSheet))
            ->exists();
    }
}
