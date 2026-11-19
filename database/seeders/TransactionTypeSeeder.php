<?php

namespace Database\Seeders;

use App\Enums\TransactionCategory as C;
use App\Models\TransactionType;
use Illuminate\Database\Seeder;

/**
 * Ledger transaction types from 2020, with spelling fixed and each one given a
 * category so reports group by category instead of hard-coded IDs.
 */
class TransactionTypeSeeder extends Seeder
{
    public function run(): void
    {
        // [name, category, derived_in_reports, description]
        $types = [
            ['Cash', C::CurrentAsset, false, null],
            ['Accounts Receivable', C::CurrentAsset, false, null],
            ['Inventory', C::CurrentAsset, true, 'Reports value stock from unsold items, not from ledger lines.'],
            ['Prepaid Expenses', C::CurrentAsset, false, null],
            ['Other Current Assets', C::CurrentAsset, false, null],
            ['Land', C::FixedAsset, false, null],
            ['Buildings', C::FixedAsset, false, null],
            ['Vehicles', C::FixedAsset, false, null],
            ['Machinery', C::FixedAsset, false, null],
            ['Computers & Software', C::FixedAsset, false, null],
            ['Other Fixed Assets', C::FixedAsset, false, null],
            ['Accounts Payable', C::CurrentLiability, false, null],
            ['Overdrafts', C::CurrentLiability, false, null],
            ['Current Lease Payable', C::CurrentLiability, false, null],
            ['Customer Repayments', C::CurrentLiability, false, null],
            // 2020 labelled this "Other Current Assets" but summed it as a liability.
            ['Other Current Liabilities', C::CurrentLiability, false, null],
            ['Long-Term Debts', C::LongTermLiability, false, null],
            ['Pension Fund Liability', C::LongTermLiability, false, null],
            // 2020 label: "Preffered Taxed Liability".
            ['Deferred Tax Liability', C::LongTermLiability, false, null],
            ['Other Long-Term Liabilities', C::LongTermLiability, false, null],
            ['Retained Earnings', C::Equity, true, 'Reports derive retained earnings from sales, not from ledger lines.'],
            ['Treasury Stock', C::Equity, false, null],
            ['Common Stock', C::Equity, false, null],
            ['Other Shareholder Equity', C::Equity, false, null],
            ['Other Income', C::Income, false, null],
            ['Rent', C::Expense, false, null],
            ['Provision for Income Taxes', C::Expense, false, null],
        ];

        foreach ($types as $index => [$name, $category, $derived, $description]) {
            TransactionType::updateOrCreate(['name' => $name], [
                'category' => $category,
                'derived_in_reports' => $derived,
                'description' => $description,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
