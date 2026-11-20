# Yande Gadgets 2026 rebuild plan

Status: draft, November 2026. Background on the 2020 app is in [CLAUDE.md](../CLAUDE.md).

## Decision

Rebuild on the latest stable Laravel, in a fresh skeleton, and carry the 2020 domain logic over. Don't upgrade in place.

- The 2020 app is small: 22 controllers, about 3,900 lines of PHP and 5,200 of Blade. Its only tests are placeholders.
- Upgrading in place means passing through every major Laravel version from 7 onwards, plus PHP 7.2 → 8.x. Four of its packages are abandoned or outdated: `laravelcollective/html`, `hardevine/shoppingcart`, `cartalyst/stripe-laravel` and `beyondcode/laravel-websockets` 1.x.
- What limits growth is the schema and the hard-coded IDs, not the framework. A rebuild is the cheapest point to fix them.
- The shop paused and there's no live database in the repo, so the schema can be redesigned freely. Seed data (categories, conditions, transaction types) carries over.

Keep the 2020 version: tag it `v1-2020` on `master`, build the new app on a `rebuild` branch, and merge once it reaches parity.

## What stays the same (business rules)

- Buy in the UK, ship to Ghana, sell in store in Accra.
- Each physical item is tracked individually. Its condition, cost and sale belong to that unit.
- Online payments are deposits for requests only. The real sale is negotiated and recorded in store.
- Negotiation floor: the sale price can't go below 70% of the listed GHS price. This becomes a setting, not a constant.
- Customers only see stock that has arrived in Ghana and isn't sold.

## Target stack

| Area                                            | 2020                            | 2026                                                                                                                                      |
| ----------------------------------------------- | ------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| Framework                                       | Laravel 6, PHP 7.2              | Laravel 13, PHP 8.3+, Node 24                                                                                                             |
| Customer-facing and staff frontend              | Blade + Bootstrap 4             | **Inertia.js + React + TypeScript**, Tailwind, shadcn/ui, from Laravel's official React starter kit. Server-side rendering on for SEO     |
| Back-office data entry (settings, ledger, CRUD) | Hand-written Blade CRUD         | **Filament** panel at `/admin`, which replaces most of the 22 controllers. Screens can move into React later if they need a custom design |
| Auth                                            | `laravel/ui`                    | The React starter kit's auth (Fortify), email verification, optional 2FA for admin                                                        |
| Roles                                           | Raw `role_id` 1–4, broken gates | `spatie/laravel-permission` + Policies, with no numeric role checks                                                                       |
| Payments                                        | Cartalyst Stripe charge         | A `PaymentGateway` interface with Paystack (mobile money + card) and/or Stripe behind it. See decision 2                                  |
| Images                                          | `public/gallery`                | `spatie/laravel-medialibrary` on S3-compatible storage (e.g. Cloudflare R2), resized automatically                                        |
| Search                                          | None                            | Laravel Scout: database driver first, Meilisearch when the catalogue grows                                                                |
| Notifications                                   | Database only                   | Database + mail, queued; SMS/WhatsApp channel later                                                                                       |
| Money                                           | Floats in GBP                   | Integer minor units (pence / pesewas) with a currency, via `brick/money`                                                                  |
| Tests                                           | None                            | PHPUnit feature tests on every money rule, Larastan level 7, Pint                                                                         |
| Hosting                                         | Heroku Procfile                 | Laravel Forge + VPS, or Laravel Cloud, with a queue worker and nightly DB backups                                                         |

## Schema redesign

Fixes for the 2020 problems, each chosen so the app can grow:

1. **Real foreign keys everywhere**, with `restrict` or `nullOnDelete` set deliberately. No more orphaned rows.
2. **Product vs stock unit.** `products` holds the catalogue entry (name, category, brand, model, specs, photos). `stock_units` holds each physical item (product_id, condition_id, cost, supplier_id, purchase date, shipment, status, sale). This keeps one row per physical item and also makes "3 × iPhone 13, grade B" a simple count, which is the bulk-buying feature the 2020 version was missing.
3. **Status enum instead of booleans.** `stock_units.status`: `purchased → in_transit → received → reserved → sold` (plus `returned` and `written_off`). It's a PHP enum, with transitions in one service class and a timestamp per step.
4. **Exchange rates with history.** `exchange_rates` (from, to, rate, effective_at). A sale stores the rate it used, the listed GHS price and the GHS price actually paid. Reports then stay correct after the rate changes. In 2020, sales were converted back to GBP and the rate was lost.
5. **Sales table.** `sales` (stock_unit_id, staff_id, customer_id nullable, price_ghs, rate_id, sold_at, payment method). This replaces overwriting `selling_Price`.
6. **Shipments.** `shipments` + `shipment_items` (FK to stock units). This replaces the session cart and the `shipped_product` pivot without FKs. Receipt is per item, with an optional staff photo, and the shipment's status is derived from its items.
7. **Requests and deposits.** `product_requests` + `payments` (polymorphic, provider, reference, amount, currency, status, webhook-confirmed). The deposit is marked paid only from a verified webhook, never from the browser redirect.
8. **Supplier offers** keep their own table, but "accept offer" creates a stock unit in one transaction.
9. **Branches / locations** (`locations` table, FK on stock units, sales, staff). Seed one row for Legon. It costs almost nothing now and avoids a migration when a second store or a UK warehouse appears.
10. **Accounting.** Keep the manual ledger, but give `transaction_types` a `category` column (current_asset, fixed_asset, current_liability, long_term_liability, equity, income, expense). Reports group by category instead of hard-coded IDs 1–28, so new types show up in reports automatically. The same applies to categories and conditions in the sales breakdown.
11. **Audit log** (`spatie/laravel-activitylog`) on stock units, sales, payments and rate changes.

## Frontend split

React (Inertia) handles everything where the experience matters and gets the full redesign:

- **Storefront:** home, browse, filters, search, product page with gallery, request-an-item flow, deposit checkout
- **Customer account:** my requests, deposit status, notifications
- **Supplier portal:** submit offers with photo upload, track their status
- **Staff tools (tablet-first):** the in-store sale screen with the live negotiation floor, receiving a shipment item by item with photos
- **Owner dashboard:** charts for sales, margin and stock age

Filament handles the plain data entry: categories, conditions, transaction types, ledger lines, wages, exchange rates, users and roles, ship companies. It's quick to build and doesn't need a bespoke design. Any screen that later needs a better experience can be rebuilt in React without touching the backend.

Inertia keeps a single Laravel app: routes, controllers, policies and validation stay in PHP, and React pages receive typed props. There's no separate API to build or secure. If a mobile app comes later, add a Sanctum API next to it.

## Phases

**Design phase (runs alongside Phase 0, 1–2 weeks)**
A brand refresh and design system: colour, type, components, keeping or reworking the Ghana-flag palette. Then key screens designed mobile-first in Figma (most customers in Accra browse on phones): home, browse, product page, request + deposit, staff sale screen, receiving screen, owner dashboard. Map the tokens into Tailwind and shadcn/ui so design and code share the same names.

Each phase ends with something usable and tested.

**Phase 0 — Foundations (1 week)** — done on the `rebuild` branch, November 2026
Tag `v1-2020`. Fresh Laravel app on `rebuild`. CI (GitHub Actions: Pint, Larastan, PHPUnit, frontend lint and type checks). Auth, roles/permissions, locations, money and enum base classes. Seeders ported from `database/seeds`.

**Phase 1 — Catalogue and stock (1–2 weeks)** — done on the `rebuild` branch, November 2026 (exchange-rate history pulled forward from Phase 3 so the shop can show cedis)
Categories, conditions, suppliers, products, stock units, images. Filament admin for all of these. React storefront built from the design system: listing, category filter, price sort, search, product page. Only received and unsold stock is visible, enforced in a query scope.

**Phase 2 — Shipments (1 week)** — done on the `rebuild` branch, November 2026
Build a shipment, confirm with carrier, cost and date. Staff mark items received, with a photo. Notifications to staff and admin.

**Phase 3 — In-store sales (1 week)** — done on the `rebuild` branch, November 2026
Staff sale screen (tablet-friendly): listed GHS price, floor price, negotiated price, payment method (cash / MoMo / card). Rate history. Tests on the floor and on the conversions.

**Phase 4 — Requests and deposits (1–2 weeks)** — done on the `rebuild` branch, November 2026, with Paystack
Customer request flow, deposit by condition, payment gateway with webhooks and idempotency, the "acquired" flow linking a request to a stock unit, refunds handled by admin.

**Phase 5 — Supplier portal (1 week)** — done on the `rebuild` branch, November 2026
Supplier onboarding, offers with photos, admin accept → stock unit.

**Phase 6 — Reports (1–2 weeks)** — done on the `rebuild` branch, November 2026
Dashboard (sales, margin, stock age, units by category/condition/location). Balance sheet and income statement driven by transaction-type categories, with negatives highlighted. CSV/PDF export. Wages prorated from staff wage periods (and the wage rate's unit — daily, weekly or monthly — stored on the record).

**Phase 7 — Launch (1 week)**
Hosting, backups, error tracking, privacy policy (Ghana Data Protection Act), staff training, a soft launch with real stock.

Rough total: 10–15 weeks part-time for one developer, plus the design phase. The full React redesign adds time; Filament for the plain admin screens keeps it realistic.

## Later, once it's running

- WhatsApp/SMS notifications for customers (the "your item has arrived" message).
- Full online reservation: pay a deposit on in-store stock to hold it for N days.
- Trade-ins: customers sell devices to the shop.
- Warranty tracking and returns per stock unit.
- A JSON API (Sanctum) for a staff mobile app or barcode/IMEI scanning.
- Multi-currency display for UK-side buying.

## Decisions needed from the owner and Richard

1. **Is there any 2020 production data to keep?** The repo has none. If an old database exists, add an import step in Phase 1.
2. ~~Payment provider.~~ Decided: Paystack (mobile money + card, GHS).
3. ~~Storefront style.~~ Decided: Inertia + React + TypeScript for the customer, supplier and staff frontend; Filament for back-office data entry.
4. **Bulk stock.** Confirm the product / stock-unit split (recommended) rather than a plain quantity column. A quantity column would lose per-item condition and cost.
5. **Hosting budget.** It decides between Forge + VPS (cheap) and Laravel Cloud (less ops work).
6. **Domain and branding.** Keep the Ghana-flag theme or refresh it.
