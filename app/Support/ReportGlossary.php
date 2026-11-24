<?php

namespace App\Support;

use App\Enums\TransactionCategory;
use App\Models\TransactionType;
use Illuminate\Support\HtmlString;

/**
 * Plain-English help for the reports, shown behind a "?" on each section,
 * total and worked-out line: what it is, where the number comes from, and
 * why it matters. Written for a shop owner, not an accountant.
 */
class ReportGlossary
{
    /** Shown under each page title. */
    public const PAGES = [
        'income-statement' => 'How much profit the shop made between two dates: what came in from sales, minus what the items cost, minus the costs of running the shop. Use it to check the business is making money, and where the money goes.',
        'balance-sheet' => 'A snapshot of the business on one day: what it owns, what it owes, and what\'s left over for you. Use it to see what the business is worth and whether it can pay what it owes.',
        'dashboard' => 'How the shop is doing for the dates you pick.',
    ];

    /**
     * A label followed by its "?", for PHP-built headings (stats, charts).
     */
    public function label(string $text, ?string $key = null): HtmlString
    {
        $button = view('components.yg-explain', ['label' => $key ?? $text])->render();

        return new HtmlString('<span class="yg-explain-label">'.e($text).$button.'</span>');
    }

    /**
     * @return array{what: string, from: string, impact: string}|null
     */
    public function explain(string $label): ?array
    {
        $entry = $this->entries()[$label] ?? null;

        if ($entry !== null) {
            return ['what' => $entry[0], 'from' => $entry[1], 'impact' => $entry[2]];
        }

        return $this->ledgerType($label);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    private function entries(): array
    {
        return [
            // Dashboard
            'dashboard:sales' => [
                'What customers paid for items in the dates you picked.',
                'Every sale recorded on the Sell screen. Voided sales are left out.',
                'Shows how busy the shop was. Profit, next to it, shows whether it paid off.',
            ],
            'dashboard:gross-profit' => [
                'Sales minus what those items cost you in the UK. The margin is profit as a share of sales.',
                'Each sale\'s price minus its stock unit\'s UK cost, at the rate saved with the sale.',
                'Running costs (rent, wages, shipping) still come out of this. See the Income statement for the full picture.',
            ],
            'dashboard:items-sold' => [
                'How many items sold, and on average how far below the listed price they went.',
                'Sales recorded on the Sell screen, compared with each item\'s listed price.',
                'A big average discount means prices may be set too high, or staff give away too much when haggling.',
            ],
            'dashboard:in-store' => [
                'Items in the store now (including reserved ones), what they cost you, and how long they\'ve been there on average.',
                'Stock units that have been checked in and not sold.',
                'Money tied up in stock. The longer it sits, the more you should think about lowering prices.',
            ],
            'dashboard:trend' => [
                'Sales and gross profit for each of the last 12 months.',
                'The same sales as the figures above, one bar per month.',
                'Shows busy and quiet months, so you can plan buying trips and cash.',
            ],
            'dashboard:what-sold' => [
                'Your sales split by category, condition or store.',
                'Sales recorded on the Sell screen in the dates you picked. Click a row for the details.',
                'Shows what sells and what makes money, so you buy more of the right things.',
            ],
            'dashboard:stock-age' => [
                'How long the items in the store have been waiting to sell.',
                'Stock units checked in and not sold, counted from the day they arrived.',
                'Old stock ties up money. Items over 90 days may need a lower price, and you should stop buying more like them.',
            ],

            // Income statement
            'Sales' => [
                'Money customers paid for items in the store.',
                'Every sale staff recorded on the Sell screen in these dates. Voided sales are left out.',
                'More sales is good, but only if the items were bought cheaply enough. Look at gross profit next.',
            ],
            'In-store sales' => [
                'The total customers paid, after haggling.',
                'Each sale\'s agreed price, from the Sell screen.',
                'The starting point for profit.',
            ],
            'Cost of sales' => [
                'What you paid for the items you sold.',
                'Each sold item\'s UK cost, turned into cedis at the rate on the day it was sold.',
                'The higher this is compared with sales, the less you make on each item.',
            ],
            'Cost of items sold' => [
                'What the shop paid in the UK for the items that sold.',
                'The cost on each stock unit, at the exchange rate saved with the sale.',
                'Paying less in the UK is the quickest way to raise profit.',
            ],
            'Gross profit' => [
                'Sales minus what the items cost.',
                'Worked out from the two sections above.',
                'If this is low, you\'re paying too much in the UK or selling too cheaply. It has to cover all the running costs below.',
            ],
            'Other income' => [
                'Money that came in other than from selling items.',
                'Kept deposits are worked out for you. The rest are ledger lines typed as income (Finance → Ledger).',
                'Adds to profit.',
            ],
            'Kept deposits' => [
                'Cancellation fees you kept when a customer cancelled after you\'d found their item, or didn\'t collect it.',
                'Worked out from cancelled customer requests and their refunds.',
                'Adds to profit. The fee percentage is in Shop settings.',
            ],
            'Expenses' => [
                'The costs of running the shop.',
                'Shipping and wages are worked out for you. Rent, tax and other costs come from the ledger (Finance → Ledger).',
                'Every cedi here comes straight off your profit. Check it for anything that grew.',
            ],
            'Shipping from the UK' => [
                'What it cost to send shipments to Ghana.',
                'The cost entered on each shipment dispatched in these dates (Shipping → Shipments).',
                'Shipping costs spread over every item in the box, so fuller boxes mean cheaper items.',
            ],
            'Staff wages' => [
                'What staff were paid for these dates.',
                'Each wage\'s daily, weekly or monthly rate (Finance → Staff wages), counted for the days in the period.',
                'A fixed cost: you pay it whether or not you sell.',
            ],
            'Net profit' => [
                'What\'s left after every cost: the shop\'s real profit.',
                'Gross profit, plus other income, minus expenses.',
                'The number to watch. Negative (red, in brackets) means the shop lost money in these dates.',
            ],

            // Balance sheet
            'Current assets' => [
                'Things the business owns that are money, or will turn into money within a year.',
                'Stock is worked out for you. Cash and the rest come from the ledger (Finance → Ledger).',
                'This is what you can use to pay bills soon.',
            ],
            'Stock' => [
                'Items you own and haven\'t sold yet: in the store, on the way, or bought in the UK.',
                'Every unsold stock unit, at what you paid for it in the UK, in cedis.',
                'Stock isn\'t cash until it sells. A lot of stock that sits for months ties up your money (see Stock age on the dashboard).',
            ],
            'Fixed assets' => [
                'Things the business owns and keeps using for years, like vehicles, shop fittings or computers.',
                'Ledger lines typed as fixed assets (Finance → Ledger).',
                'They add to what the business is worth, but you can\'t easily spend them.',
            ],
            'Total assets' => [
                'Everything the business owns.',
                'Current assets plus fixed assets.',
                'Compare it with what you owe, below.',
            ],
            'Current liabilities' => [
                'What the business owes and has to pay within a year.',
                'Customer deposits are worked out for you. Bills, overdrafts and the rest come from the ledger.',
                'If this is bigger than current assets, you may struggle to pay your bills on time.',
            ],
            'Customer deposits held' => [
                'Deposits customers paid for requests that aren\'t finished yet.',
                'Paid customer requests that haven\'t been collected or cancelled (Sales → Customer requests).',
                'This money isn\'t yours yet: it comes off the price when they buy, or goes back if you cancel.',
            ],
            'Long-term liabilities' => [
                'Debts that are paid back over more than a year, like a loan.',
                'Ledger lines typed as long-term liabilities (Finance → Ledger).',
                'Reduces what the business is worth. Watch that repayments are affordable.',
            ],
            'Total liabilities' => [
                'Everything the business owes.',
                'Current plus long-term liabilities.',
                'The less you owe, the safer the business.',
            ],
            'Net assets' => [
                'What the business owns minus what it owes: what it\'s worth on paper.',
                'Total assets minus total liabilities.',
                'Growing year on year is a good sign. It should match equity below.',
            ],
            'Equity' => [
                'The part of the business that belongs to you: what you put in, plus profit you\'ve kept in the business.',
                'Money you put in comes from the ledger. Retained earnings are worked out from every sale.',
                'Should equal net assets. If not, the difference shows on the "Not yet explained" line.',
            ],
            'Retained earnings' => [
                'All the profit the shop has made since records began, kept in the business.',
                'The net profit from the income statement, added up from the very first sale to this day.',
                'Grows when the shop makes a profit and shrinks with a loss. Money you take out for yourself should be entered in the ledger.',
            ],
            'Not yet explained by the ledger' => [
                'The gap between net assets and equity.',
                'Net assets minus equity.',
                'Usually opening balances (cash in hand, money you put in) that haven\'t been entered yet. Add them in Finance → Ledger and this line goes away.',
            ],
        ];
    }

    /**
     * Ledger lines: one sentence on the category, and where it's entered.
     *
     * @return array{what: string, from: string, impact: string}|null
     */
    private function ledgerType(string $label): ?array
    {
        $type = TransactionType::where('name', $label)->first();

        if ($type === null) {
            // Section totals such as "Total current assets".
            if (str_starts_with($label, 'Total ')) {
                $section = ucfirst(substr($label, 6));
                $entry = $this->entries()[$section] ?? null;

                return $entry === null ? null : [
                    'what' => 'All of the '.mb_strtolower($section).' above, added up.',
                    'from' => 'The lines in this section.',
                    'impact' => $entry[2],
                ];
            }

            return null;
        }

        [$what, $impact] = match ($type->category) {
            TransactionCategory::CurrentAsset => ['Something the business owns that\'s money, or soon will be.', 'Adds to what the business owns.'],
            TransactionCategory::FixedAsset => ['Something the business owns and uses for years.', 'Adds to what the business is worth.'],
            TransactionCategory::CurrentLiability => ['Money the business owes within a year.', 'Reduces what the business is worth until it\'s paid.'],
            TransactionCategory::LongTermLiability => ['Money the business owes over more than a year.', 'Reduces what the business is worth until it\'s paid.'],
            TransactionCategory::Equity => ['Part of the business that belongs to the owner.', 'Should balance against net assets.'],
            TransactionCategory::Income => ['Money that came in, other than from sales.', 'Adds to profit.'],
            TransactionCategory::Expense => ['A cost of running the shop.', 'Comes straight off profit.'],
        };

        return [
            'what' => trim($what.' '.($type->description ?? '')),
            'from' => "The \"{$type->name}\" lines you've entered in Finance → Ledger, added up to this date.",
            'impact' => $impact,
        ];
    }
}
