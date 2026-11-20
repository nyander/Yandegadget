<?php

namespace App\Enums;

/**
 * Groups ledger transaction types for the balance sheet and income statement,
 * so reports no longer depend on hard-coded transaction type IDs.
 */
enum TransactionCategory: string
{
    case CurrentAsset = 'current_asset';
    case FixedAsset = 'fixed_asset';
    case CurrentLiability = 'current_liability';
    case LongTermLiability = 'long_term_liability';
    case Equity = 'equity';
    case Income = 'income';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::CurrentAsset => 'Current asset',
            self::FixedAsset => 'Fixed asset',
            self::CurrentLiability => 'Current liability',
            self::LongTermLiability => 'Long-term liability',
            self::Equity => 'Equity',
            self::Income => 'Income',
            self::Expense => 'Expense',
        };
    }

    public function isBalanceSheet(): bool
    {
        return ! in_array($this, [self::Income, self::Expense], true);
    }
}
