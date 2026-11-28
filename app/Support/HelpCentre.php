<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * The owner's guide in the admin panel. Articles are Markdown files in
 * resources/help. Inside them, `help:slug` links to another article and
 * `admin:path` links to a page in the panel (`admin:` alone is the dashboard).
 */
class HelpCentre
{
    /**
     * In reading order.
     *
     * @var array<string, array{title: string, summary: string, group: string}>
     */
    private const TOPICS = [
        'welcome' => ['title' => 'How the app works', 'summary' => 'The big picture, from buying in the UK to selling in Accra.', 'group' => 'Start here'],
        'first-week' => ['title' => 'Getting set up', 'summary' => 'What to fill in before customers use the site.', 'group' => 'Start here'],
        'daily' => ['title' => 'Your daily and weekly routine', 'summary' => 'What to check, and when.', 'group' => 'Start here'],
        'stock' => ['title' => 'Adding stock you\'ve bought', 'summary' => 'Products, items, conditions, costs and prices.', 'group' => 'Running the shop'],
        'shipments' => ['title' => 'Sending a shipment to Accra', 'summary' => 'Building, dispatching and checking in shipments.', 'group' => 'Running the shop'],
        'selling' => ['title' => 'Selling in the store', 'summary' => 'The lowest price, reserved items and voiding mistakes.', 'group' => 'Running the shop'],
        'reservations' => ['title' => 'Selling online: reservations', 'summary' => 'Customers reserving and paying by mobile money, and collecting in store.', 'group' => 'Running the shop'],
        'requests' => ['title' => 'Customer requests and deposits', 'summary' => 'Finding items for customers, and refunds.', 'group' => 'Running the shop'],
        'buying' => ['title' => 'Deciding what to buy', 'summary' => 'The details behind each row, and your buying list.', 'group' => 'Running the shop'],
        'suppliers' => ['title' => 'Offers from UK suppliers', 'summary' => 'Buying or declining what suppliers offer.', 'group' => 'Running the shop'],
        'prices' => ['title' => 'Prices and the exchange rate', 'summary' => 'Pounds in, cedis out.', 'group' => 'Money'],
        'money' => ['title' => 'Money: ledger, wages and reports', 'summary' => 'What to enter, and reading the reports.', 'group' => 'Money'],
        'people' => ['title' => 'Staff and customer accounts', 'summary' => 'Roles, adding people, and data requests.', 'group' => 'People and settings'],
        'settings' => ['title' => 'Shop settings', 'summary' => 'Lowest price, cancellation fee and collection days.', 'group' => 'People and settings'],
        'help' => ['title' => 'When something goes wrong', 'summary' => 'Common problems and how to get help.', 'group' => 'People and settings'],
        'glossary' => ['title' => 'Words used in the app', 'summary' => 'What each term means.', 'group' => 'People and settings'],
    ];

    /**
     * @return array<string, array{title: string, summary: string, group: string}>
     */
    public function topics(): array
    {
        return self::TOPICS;
    }

    /**
     * @return array<string, array<string, array{title: string, summary: string, group: string}>>
     */
    public function grouped(): array
    {
        $groups = [];

        foreach (self::TOPICS as $slug => $topic) {
            $groups[$topic['group']][$slug] = $topic;
        }

        return $groups;
    }

    public function exists(?string $slug): bool
    {
        return $slug !== null && array_key_exists($slug, self::TOPICS);
    }

    /**
     * @return array{title: string, summary: string, group: string}
     */
    public function topic(string $slug): array
    {
        return self::TOPICS[$slug];
    }

    public function url(?string $slug = null): string
    {
        return url('/admin/help'.($slug !== null ? '?topic='.$slug : ''));
    }

    /**
     * The next article in reading order, for the "Next" link.
     */
    public function next(string $slug): ?string
    {
        $slugs = array_keys(self::TOPICS);
        $index = array_search($slug, $slugs, true);

        return $index === false ? null : ($slugs[$index + 1] ?? null);
    }

    public function render(string $slug): HtmlString
    {
        $markdown = (string) file_get_contents(resource_path("help/{$slug}.md"));

        $markdown = preg_replace_callback(
            '/\]\((help|admin):([a-z0-9\-\/]*)\)/',
            fn (array $m) => ']('.($m[1] === 'help' ? $this->url($m[2]) : url('/admin'.($m[2] === '' ? '' : '/'.$m[2]))).')',
            $markdown,
        ) ?? $markdown;

        return new HtmlString(Str::markdown($markdown, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]));
    }
}
