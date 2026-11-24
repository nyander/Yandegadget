<?php

namespace App\Support;

/**
 * Step-by-step tours of the admin pages, shown by resources/js/admin-tour.ts.
 * Each step points at part of the page with a CSS selector (or null for a
 * message in the middle of the screen). Steps whose element isn't on the page
 * are skipped, so a tour still works on an empty table.
 */
class AdminTours
{
    private const HEADER_ACTIONS = '.fi-header-actions-ctn';

    private const SEARCH = '.fi-ta-search-field';

    private const FILTERS = '.fi-ta-filters-dropdown';

    private const ROW_ACTIONS = '.fi-ta-row .fi-ta-actions';

    private const TABLE = '.fi-ta';

    /**
     * @return array{key: string, steps: list<array{element: string|null, title: string, text: string}>, panels: array<string, list<array{element: string|null, title: string, text: string}>>}|null
     */
    public function forRoute(?string $route): ?array
    {
        if ($route === null) {
            return null;
        }

        $key = str_replace('filament.admin.', '', $route);
        $steps = $this->tours()[$key] ?? null;

        if ($steps === null) {
            return null;
        }

        return [
            'key' => $key,
            'steps' => $this->format($steps),
            'panels' => array_map(fn (array $panel) => $this->format($panel), $this->panels()),
        ];
    }

    /**
     * @param  list<array{0: string|null, 1: string, 2: string}>  $steps
     * @return list<array{element: string|null, title: string, text: string}>
     */
    private function format(array $steps): array
    {
        return array_map(fn (array $step) => [
            'element' => $step[0],
            'title' => $step[1],
            'text' => $step[2],
        ], $steps);
    }

    /**
     * Tips for panels and pop-ups, keyed by their data-tour-panel attribute.
     * They show the first time a panel opens, and whenever it opens during a
     * page tour (which then carries on when the panel closes). Selectors are
     * looked up inside the panel.
     *
     * @return array<string, list<array{0: string|null, 1: string, 2: string}>>
     */
    public function panels(): array
    {
        return [
            'what-sold-details' => [
                ['[data-tour="panel-suggestion"]', 'The suggestion', 'Whether to buy more, hold off or buy carefully, and why. It aims for about two months of stock, counting what\'s already on the way.'],
                ['[data-tour="panel-figures"]', 'The figures', 'How much these made, how fast they sell, and how many you already have in the store, on the way or on your buying list.'],
                ['[data-tour="panel-best-sellers"]', 'Best sellers', 'What sold most, with what you paid in the UK on average: a good price to beat on your next trip.'],
                ['.fi-modal-footer-actions', 'Add to buying list', '<strong>Add to buying list</strong> saves the best seller to your list, already filled in. <strong>Close</strong> takes you back to where you were.'],
            ],
            'stock-age-details' => [
                ['[data-tour="panel-advice"]', 'What to do', 'Advice for stock of this age, for example when to lower a price.'],
                ['[data-tour="panel-items"]', 'The items', 'Oldest first. Click a reference to open the item, for example to change its price.'],
            ],
            'add-to-buying-list' => [
                ['.fi-modal-content', 'Add to buying list', 'Filled in from the sales you were looking at: the best seller, how many to buy, and the most to pay for each in the UK. Change anything, then click <strong>Add to list</strong>.'],
            ],
        ];
    }

    /**
     * @return array<string, list<array{0: string|null, 1: string, 2: string}>>
     */
    private function tours(): array
    {
        $guide = fn (string $topic, string $label) => '<a href="'.e(app(HelpCentre::class)->url($topic)).'">'.e($label).' →</a>';

        return [
            'pages.dashboard' => [
                [null, 'Welcome to the back office', 'This is where you run the shop: stock, shipments, sales, money and people. This short tour shows you around. Use <strong>Next</strong> to continue, or <strong>Skip tour</strong> to stop.'],
                ['[data-tour="getting-started"]', 'Getting started', 'Work through this list first. Each step ticks itself off when it\'s done. <strong>Open</strong> takes you to the page; <strong>How?</strong> explains it.'],
                ['[data-tour="dates"]', 'Pick the dates', 'The figures below cover these dates. Leave them empty for this month so far.'],
                ['.fi-wi-stats-overview', 'Sales at a glance', 'What you sold, your profit after what the items cost, and how much stock is in the store.'],
                ['[data-tour="stock-age"]', 'Stock age', 'How long items have been in the store. Items over 90 days might need a lower price. <strong>Click a row</strong> to see the items.'],
                ['[data-tour="what-sold"]', 'What sold', 'Your sales by category, condition or store. <strong>Click any row</strong> to see the sales behind it, a buying suggestion, and <strong>Add to buying list</strong>. '.$guide('buying', 'Read more')],
                ['[data-tour="what-sold-tools"]', 'Change the view', 'Pick <strong>By category</strong>, <strong>By condition</strong> or <strong>By store</strong>. Narrow it down, for example to phones only, and choose the order. You can also click a column heading to sort by it.'],
                ['.fi-sidebar', 'The menu', 'Everything else is here, grouped: <strong>Reports</strong>, <strong>Sales</strong>, <strong>Catalogue</strong>, <strong>Shipping</strong>, <strong>Buying</strong>, <strong>Finance</strong> and <strong>Settings</strong>.'],
                ['.fi-topbar-database-notifications-btn', 'Notifications', 'The bell shows new deposits, supplier offers and checked-in shipments.'],
                ['#yg-help-menu', 'Help is always here', 'Open this to take the tour of whichever page you\'re on, read the guide, or turn these tips off.'],
            ],
            'pages.help' => [
                [null, 'The guide', 'Short articles on every part of the back office, in the order you\'ll need them.'],
                ['.yg-help__nav', 'All the topics', 'Pick a topic here. Start with <strong>How the app works</strong> and <strong>Getting set up</strong>.'],
            ],
            'resources.products.index' => [
                [null, 'Products', 'A product is <em>what</em> something is, like "iPhone 13 128GB". The actual items you own sit inside each product. '.$guide('stock', 'Read more')],
                [self::HEADER_ACTIONS, 'Add a product', 'Use this for something you haven\'t sold before. Add a name, category, description and photos.'],
                [self::SEARCH, 'Find a product', 'Type part of the name. If the product is already here, open it and add the new item to it instead of making a new product.'],
                [self::ROW_ACTIONS, 'Open a product', 'Edit it to change its details or photos, and to add items under <strong>Stock units</strong>.'],
            ],
            'resources.products.edit' => [
                [null, 'Editing a product', 'Change what customers see: name, description, photos and whether it\'s on the website.'],
                ['.fi-sc-form', 'Product details', 'Turn off <strong>Published</strong> to hide it from the website. <strong>Featured</strong> puts it on the home page.'],
                ['.fi-resource-relation-manager', 'Stock units', 'Each physical item you own. Use <strong>Add unit</strong> for one you\'ve bought: its condition, cost and list price in pounds. Bought a batch of the same thing? <strong>Add several</strong> records them all at once, each with its own reference.'],
            ],
            'resources.stock-units.index' => [
                [null, 'Stock units', 'Every physical item, one row each, with its status: in the UK, in transit, in store, reserved or sold. '.$guide('stock', 'Read more')],
                [self::HEADER_ACTIONS, 'Add items', '<strong>Add several</strong> records a batch of the same product bought together: same grade, cost and price, with a serial number each if they have one.'],
                [self::FILTERS, 'Filter the list', 'Show only items in the store, in transit, and so on.'],
                [self::ROW_ACTIONS, 'Change an item', 'Edit its price or condition, or use <strong>Move</strong> to write off something lost or broken.'],
            ],
            'resources.shipments.index' => [
                [null, 'Shipments', 'Boxes of items sent from the UK to Accra. '.$guide('shipments', 'Read more')],
                [self::HEADER_ACTIONS, 'Start a shipment', 'Create one, then add the items going in the boxes.'],
                [self::TABLE, 'Your shipments', 'Draft ones are still being packed. In-transit ones are on the way: <strong>Track</strong> opens the carrier\'s tracking page. Received ones have been checked in by staff.'],
            ],
            'resources.shipments.edit' => [
                [null, 'A shipment', 'Add items while it\'s a draft, then dispatch it when it leaves.'],
                ['.fi-resource-relation-manager', 'Items in the boxes', 'Use <strong>Add items</strong> to choose what\'s going. Only items bought in the UK and not already on a shipment can be added.'],
                [self::HEADER_ACTIONS, 'Dispatch and track', 'When it leaves, click <strong>Dispatch</strong> and enter the carrier, tracking number, cost and date. Staff in Accra are told it\'s coming. Then <strong>Track</strong> opens the carrier\'s page for it.'],
            ],
            'resources.product-requests.index' => [
                [null, 'Customer requests', 'Customers asking you to find something, with a deposit paid. '.$guide('requests', 'Read more')],
                [self::FILTERS, 'Filter by status', 'Show only the ones you\'re still looking for.'],
                ['.fi-ta-row', 'Open a request', '<strong>Click a request</strong> to see everything about it, with the next step as buttons at the top.'],
            ],
            'resources.product-requests.view' => [
                [null, 'One request', 'What the customer wants, their details, the deposit, the item found for them and what\'s happened so far.'],
                [self::HEADER_ACTIONS, 'What to do next', '<strong>Match an item</strong> when you\'ve found one: it\'s reserved and the customer is told. <strong>Decline</strong> if you can\'t get it: they get all of the deposit back. <strong>Cancel</strong> is for when the customer changes their mind, and shows the refund before you confirm.'],
            ],
            'resources.sales.index' => [
                [null, 'Sales', 'Every sale staff recorded in the store, with the price, the lowest allowed price and the rate on the day.'],
                [self::SEARCH, 'Find a sale', 'Search by reference, item or customer.'],
                [self::ROW_ACTIONS, 'Fix a mistake', 'Sales can\'t be edited. Use <strong>Void</strong> with a reason; the item goes back on sale and staff record it again.'],
            ],
            'resources.payments.index' => [
                [null, 'Online payments', 'Every deposit customers paid through Paystack, and any refunds. Use this to check a customer\'s payment went through.'],
            ],
            'resources.buying-list.index' => [
                [null, 'Your buying list', 'What to look for on your next UK buying trip. Add items here, or from the dashboard\'s <strong>What sold</strong> details. '.$guide('buying', 'Read more')],
                [self::FILTERS, 'To buy, bought, not needed', 'The list shows what\'s still <strong>To buy</strong>. Change the filter to see the rest.'],
                [self::ROW_ACTIONS, 'Tick it off', 'Mark an item <strong>Bought</strong> when you\'ve bought it, or <strong>Not needed</strong> if you\'ve changed your mind.'],
            ],
            'resources.supplier-offers.index' => [
                [null, 'Supplier offers', 'Items UK suppliers want to sell you. '.$guide('suppliers', 'Read more')],
                [self::ROW_ACTIONS, 'Buy or decline', '<strong>Buy</strong> adds the item to your stock at the agreed price, ready to ship. <strong>Decline</strong> tells the supplier why.'],
            ],
            'resources.ledger-entries.index' => [
                [null, 'The ledger', 'Money the app can\'t work out for itself: rent, bills, cash, loans, equipment. Sales, item costs and shipping are already counted. '.$guide('money', 'Read more')],
                [self::HEADER_ACTIONS, 'Add an entry', 'Choose the type, date and amount. Use a minus amount to reduce a balance, like paying back part of a loan.'],
                [self::FILTERS, 'Filter', 'By type or dates.'],
            ],
            'resources.staff-wages.index' => [
                [null, 'Staff wages', 'Each person\'s wage per day, week or month. The reports share it out over the dates you look at.'],
                [self::HEADER_ACTIONS, 'Add a wage', 'When a wage changes, give the old one an end date, then add the new one.'],
            ],
            'pages.income-statement' => [
                [null, 'Income statement', 'Your profit for a period: sales, minus what the items cost, minus shipping, wages and expenses.'],
                ['[data-tour="dates"]', 'Choose the dates', 'The statement updates as you change them. <strong>Today</strong> next to a date jumps to today.'],
                ['.yg-statement__header', 'Reading it', 'Read from the top: sales, then what the items cost, then other income and expenses. Negative numbers are red, in brackets.'],
                ['[data-tour="total-gross-profit"]', 'Gross profit', 'Sales minus what the items cost you in the UK.'],
                ['[data-tour="total-net-profit"]', 'Net profit', 'What\'s left after shipping, wages and every other expense. This is the number to watch.'],
                [self::HEADER_ACTIONS, 'Download', 'Save it as a PDF for your records, or a CSV to open in Excel.'],
            ],
            'pages.balance-sheet' => [
                [null, 'Balance sheet', 'What the business owns and owes on a given day.'],
                ['[data-tour="dates"]', 'Pick the day', 'The balance sheet is for the end of this day. <strong>Today</strong> jumps to today.'],
                ['.yg-statement__header', 'Reading it', 'What you own (assets) comes first, then what you owe (liabilities), then equity. Stock, customer deposits and profit to date are worked out for you; the rest comes from the ledger.'],
                ['[data-tour="total-net-assets"]', 'Net assets', 'What you own minus what you owe: what the business is worth on paper.'],
                ['[data-tour="unexplained"]', 'Not yet explained', 'The gap between net assets and equity. It\'s usually opening balances (cash in hand, money put into the business) that haven\'t been entered in the ledger yet. Once they are, this line goes away.'],
                [self::HEADER_ACTIONS, 'Download', 'PDF or CSV.'],
            ],
            'resources.exchange-rates.index' => [
                [null, 'Exchange rates', 'How many cedis one pound is worth. Every cedi price comes from the latest rate. '.$guide('prices', 'Read more')],
                [self::HEADER_ACTIONS, 'Add today\'s rate', 'Add a new rate when it moves; don\'t edit old ones. Past sales keep the rate they were sold at.'],
            ],
            'resources.conditions.index' => [
                [null, 'Conditions', 'The grades items are sold in, and the deposit a customer pays to request each one.'],
                [self::ROW_ACTIONS, 'Change a deposit', 'Edit a grade to change its deposit.'],
            ],
            'pages.shop-settings' => [
                [null, 'Shop settings', 'The rules the app follows when staff sell and customers request items. '.$guide('settings', 'Read more')],
                ['input[id*="negotiation_floor_percent"]', 'Lowest price', 'Staff can\'t sell below this share of the listed cedi price. The box underneath shows what that means for a GH₵1,000 item as you type.'],
                ['input[id*="request_cancellation_fee_percent"]', 'Cancellation fee', 'What you keep from a deposit if a customer cancels after you\'ve found their item.'],
                ['input[id*="request_collection_days"]', 'Days to collect', 'How long a found item stays reserved before it goes back on sale.'],
                ['input[id*="request_collection_reminder_days"]', 'Reminder', 'Customers get an email this many days before their item goes back on sale, so fewer forget to collect.'],
                ['[data-tour="setting-changes"]', 'Change history', 'Every change, who made it and what it was before.'],
            ],
            'resources.users.edit' => [
                [null, 'One person', 'Their name, email, role and store. '.$guide('people', 'Read more')],
                [self::HEADER_ACTIONS, 'Passwords and closing', '<strong>Set password</strong> sets one for them (<strong>Generate</strong> makes a strong one). <strong>Email a password link</strong> lets them choose their own. <strong>Close account</strong> removes their details if they ask.'],
            ],
            'resources.users.index' => [
                [null, 'People', 'Everyone with an account: you, staff, customers and suppliers. '.$guide('people', 'Read more')],
                [self::HEADER_ACTIONS, 'Add a person', 'For staff and suppliers. Email them a link to choose their password, or set one for them.'],
                [self::ROW_ACTIONS, 'Change someone', 'Change their role or store. To delete a customer\'s data when they ask, open them and use <strong>Close account</strong>.'],
            ],
        ];
    }
}
