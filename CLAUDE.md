# Yande Gadgets — project briefing

## Current state: 2026 rebuild (read first)

The 2026 rebuild is on `master` (merged in PR #53, November 2026); all phases in `docs/REBUILD_PLAN.md` are done in code, and going live is tracked in `docs/LAUNCH_CHECKLIST.md`. The 2020 Laravel 6 app described further down is preserved at tag `v1-2020`. Use the 2020 sections for business rules and history, not for code conventions. Local setup is in `README.md`. Planned growth (clothing, bulk importing from Alibaba and similar: departments, variants and quantity stock, multi-currency costs, purchase orders, landed cost) is written up in `docs/EXPANSION_PLAN.md`; read it before changing how stock, costs or buying work. Keep the project outside iCloud-synced folders (it lives in `~/Code/Yandegadget`).

Rebuild stack and conventions:

- Laravel 13, PHP 8.4+ (the locked Symfony 8 packages need it), Laravel's React starter kit: Inertia v3, React 19, TypeScript, Tailwind 4, shadcn/ui, Wayfinder for typed routes, Fortify auth (email verification, registration, 2FA, passkeys, password confirmation). Filament 5 is the back office at `/admin` (resources in `app/Filament/Resources`). It has no login page of its own: guests go to the main Fortify login, and `User::canAccessPanel()` admits verified users with an admin-level permission.
- Node 24 (`.nvmrc`). The frontend toolchain (`vp`, Vite+) fails on Node 20.
- Roles and permissions: `spatie/laravel-permission`, defined in `app/Enums/Role.php` and `app/Enums/Permission.php`. Authorise with permissions (`$user->can(Permission::RecordSales->value)`), never role IDs or names in business logic. Self-registration assigns `customer`.
- Money: integer minor units plus a currency column, exposed as `Brick\Money\Money` through `App\Casts\MoneyCast`. Never floats.
- Enums for statuses and categories: `StockStatus` (with allowed transitions), `LocationType`, `TransactionCategory`.
- Reference data (roles, permissions, locations, categories, conditions, transaction types) is seeded by `ReferenceDataSeeder`, which is idempotent and runs on every deploy. Tests seed it automatically via `tests/TestCase.php`. `DatabaseSeeder` also adds local demo users, one per role, at `*@example.test` with password `password`.
- Catalogue: `Product` is the catalogue entry (soft-deleted, slug route key, Scout-searchable, `gallery` photos). `StockUnit` is one physical item with its own condition, cost and GBP list price (`photos`), moved through `StockStatus` only via `StockUnit::transitionTo()`. Selling isn't allowed from the admin panel; that comes with the in-store sale screen.
- Prices: costs and list prices are GBP. `App\Services\Pricing` converts to GHS at the latest `ExchangeRate` (append-only history), falling back to GBP if no rate is set. The shop never sends cost, supplier or GBP prices to the browser (`App\Http\Presenters\ShopProductPresenter`).
- Shop visibility: `Product::visibleInShop()` (published, active category, at least one received or reserved unit). Enforced in queries, not in the React pages.
- Photos: spatie/laravel-medialibrary on the `public` disk (`MEDIA_DISK`), with `thumb` and `large` WebP conversions generated straight away. Run `php artisan storage:link` locally.
- Search: Laravel Scout with the `database` driver (`SCOUT_DRIVER`). Switch to Meilisearch when the catalogue grows.
- Shipments: `Shipment` (draft → in transit → received, references like `SH-0001`) with `ShipmentItem` rows linking stock units. Every change goes through `App\Services\ShipmentService` (add/remove items, dispatch, check in), which throws `ShipmentRuleViolation` with a message safe to show staff. Admins build and dispatch shipments in Filament; staff check items in at `/staff/shipments` (React, tablet-first, with arrival photos). A missing item stays in transit; damaged items go into the store with a note.
- Stock intake: "Add several" (`App\Filament\Resources\StockUnits\Actions\AddSeveralUnitsAction`, on a product's stock units and on Stock units) creates up to 50 units of one product with shared details and one serial per line, through `App\Services\StockIntake`. Shipment tracking: `Shipment::trackingUrl()` fills `{number}` in the carrier's `tracking_url`; a Track action on shipments and a link on the staff check-in screen.
- Sales: `Sale` (references like `SA-000001`) is created only by `App\Services\SaleService::record()`, which converts the GBP list price at the current rate, enforces the negotiation floor (admin setting, 70% by default, rounded up to the pesewa) and copies the list price, listed price, floor, rate and floor percent onto the sale. Sales are never edited or deleted, only voided (`SaleService::void()`), which puts the item back in store. Staff sell at `/staff/sell` (React); admins see and void sales in Filament.
- Customer requests and deposits: `ProductRequest` (`RQ-00001`: awaiting deposit → open → sourced → ready in store → fulfilled, or cancelled) through `App\Services\ProductRequestService`. Deposits come from the condition (any currency) and are charged in GHS at the current rate. Matching a unit already in store reserves it; matching one still in the UK reserves it when `ShipmentService::checkIn()` receives it. When the reserved unit is sold, `SaleService` applies the deposit (`sales.deposit_applied_amount`) and fulfils the request; voiding the sale puts both back. Refunds follow `App\Services\RefundPolicy`: full refund if the shop cancels (`CancellationCause::Shop`) or the customer cancels before the item is found; once found, the cancellation fee (Shop settings, 25% by default) is kept, also when the item isn't collected within the collection window (14 days by default; `requests:release-uncollected` runs daily). Partial refunds go through Paystack; kept fees show as "Kept deposits" income.
- Mail: Resend in production (`MAIL_MAILER=resend`, `RESEND_API_KEY`), `log` locally.
- Payments: `App\Services\Payments\PaymentGateway` with `PaystackGateway` and a local-only `FakeGateway` (`PAYMENTS_DRIVER=paystack|fake`). `PaymentService` only marks a payment paid after verifying it with the provider (on the callback or a signed `charge.success` webhook at `POST /webhooks/paystack`, exempt from CSRF), checks amount and currency, logs every webhook in `webhook_calls` and skips duplicates. `PaymentSucceeded` is handled by `App\Listeners\HandleDepositPaid`.
- Suppliers: a user with the supplier role fills in their business (`suppliers.user_id`) at `/supplier/profile` (enforced by `EnsureSupplierProfile`), then offers items with photos at `/supplier/offers`. `App\Services\SupplierOfferService` handles submit, withdraw, accept and decline. Accepting creates (or reuses) a `Product` and a purchased `StockUnit` at the agreed price, copying the photos, ready to add to a shipment.
- Users: admins manage people in Filament (Settings → Users): roles, store location, and "Add a person", which either sends a set-your-password email or takes a password the admin sets. On a person's page, "Set password" (`App\Services\PasswordSetter`: signs them out everywhere, emails `PasswordSetByShop` without the password; not for your own account) and "Email a password link". "Generate" fills a strong, readable password (`App\Support\PasswordGenerator`, e.g. `Kp7m-Xq3r-Tz9w`) that passes the production rules. Users are never deleted, and admins can't remove their own admin role.
- Reports: the Filament dashboard (sales, margin, stock age, what sold by category, condition and store, filtered by date) and the Income statement and Balance sheet pages, each with CSV and PDF (dompdf) downloads. Built by `App\Services\Reports\*` into an `App\Reports\Statement`, shown in GHS with negatives in red brackets. Item costs convert at the rate copied onto the sale; other GBP amounts at the rate in force on the day (`ExchangeRate::asOf`). Sales, item costs, shipping, stock, customer deposits and retained earnings come from their own records. Everything else is a `LedgerEntry` (signed amounts, typed by `TransactionType` and its `TransactionCategory`). `StaffWage` stores a daily, weekly or monthly rate, prorated by `WageCalculator` (a full calendar month costs exactly the monthly rate). The balance sheet shows any gap between net assets and equity as "Not yet explained by the ledger" rather than forcing it to balance.
- Launch: `docs/DEPLOYMENT.md` (Forge + VPS or Laravel Cloud), `docs/LAUNCH_CHECKLIST.md`, `docs/STAFF_GUIDE.md`. `scripts/deploy.sh` deploys on a VPS. `php artisan shop:create-admin` creates the first admin. Nightly spatie/laravel-backup (database + photos) to `BACKUP_DISKS`, scheduled in `routes/console.php`; Sentry when `SENTRY_LARAVEL_DSN` is set. `SecurityHeaders` middleware, HTTPS forced in production, `TRUSTED_PROXIES` read in `AppServiceProvider`. Outside debug mode the shop renders `resources/js/pages/error.tsx` for 403/404/429/500/503 (not in `/admin`). `/privacy` and `/terms` (contact details from `config/shop.php`), `/sitemap.xml` and a generated `/robots.txt`. `AccountCloser` (Users → Close account) removes a person's details on request; users are still never deleted.
- What sold (dashboard): one table at a time (category, condition or store tabs), filtered by the other two and sorted by column; `SalesReport::breakdown()` and `summary()` take `$only` filters, and the row details follow them.
- Buying decisions: rows in the dashboard's "What sold" and "Stock age" open a slide-over (widget `details` actions) built by `App\Services\Reports\BuyingInsights`: the sales behind the row, best sellers with average UK cost, stock in store and on the way, and a suggestion aiming for `MONTHS_OF_COVER` (2) months of stock, or "buy carefully" under a 15% margin. "Add to buying list" (a nested `CreateAction`) saves a prefilled `BuyingListItem` (Buying → Buying list, statuses `BuyingListStatus`), managed under the catalogue permission. Guide article `resources/help/buying.md`.
- Owner's guide: `/admin/help` (`App\Filament\Pages\Help`), Markdown articles in `resources/help` listed in `App\Support\HelpCentre` (`help:slug` and `admin:path` links, checked by `OwnerGuideTest`). Keep it in step when screens change. The dashboard's "Getting started" checklist is `App\Support\GettingStarted`. Page tours (driver.js 1.10.2, `resources/js/admin/tour.ts`, loaded by a `BODY_END` render hook) use steps from `App\Support\AdminTours`, keyed by admin route name and pointing at Filament classes or `data-tour` attributes; tips can be turned off in the top-bar help menu (stored in the browser). Tours follow the person: opening a Filament modal pauses the page tour and runs that panel's tips (`AdminTours::panels()`, keyed by a `data-tour-panel` attribute, selectors looked up inside the modal), and closing it resumes the page tour at the same step; after each Livewire commit the highlight re-finds its element. Widgets and relation managers the tours point at are not lazy-loaded.
- Report help: `App\Support\ReportGlossary` explains each statement section, total and line (what it is, where it comes from, why it matters) behind a `?` (`<x-yg-explain>`, Filament tooltip), on screen only, not in the PDF; `ReportGlossary::PAGES` gives the report pages their subheadings. New statement lines need an entry (`FinancialStatementsTest`).
- Date boxes: every Filament `DatePicker` gets a "Today" hint action (`AdminPanelProvider::boot()`).
- Settings: `App\Services\ShopSettings` (key/value `settings` table, cached, code defaults), edited on the Filament "Shop settings" page, which shows a live example under each field. `set($key, $value, $by)` records changes in `setting_changes` (shown as the page's change history). `requests:release-uncollected` (daily) first emails `CollectionReminder` to customers `COLLECTION_REMINDER_DAYS` (3) days before their collect-by date, once per request (`product_requests.collection_reminded_at`, reset when a unit is reserved again).
- Live updates (polling, no websockets): every Filament table polls every 5s (`Table::configureUsing` in `AdminPanelProvider::boot()`, `LIST_REFRESH`), dashboard widgets every 30s (`DASHBOARD_REFRESH`, `wire:poll.30s` on the custom widgets), the bell every 10s, and the request view page every 10s (`filament.live-refresh` footer). React pages use `useLiveRefresh(seconds, props)` (`resources/js/hooks/use-live-refresh.ts`, Inertia `usePoll` partial reloads, throttled in background tabs) on the staff shipment, sell and customer request/account/supplier pages. Forms are never polled. The tours cope with redraws (no step animation, re-highlight only when the element is replaced).
- Notifications: database + mail, queued. Database entries use Filament's format (shown in the admin bell) plus `url` and `shipment_id` keys for the React side. `composer dev` runs a queue worker.
- Filament closures get their arguments injected by name: use `$query`, `$record`, `$state`, `$get`, `$set`, `$data`, not shortened names, or you'll get an empty object.
- Local demo data: `DatabaseSeeder` adds `DemoCatalogueSeeder` (sample stock, a demo rate of 15 GHS per GBP, and one in-transit shipment) outside production.
- Languages: the shop, sign-in and customer pages offer English, Twi, Gã, Eʋegbe and Français (`config/shop.php` `locales`). The switcher posts to `POST /language`, which sets an unencrypted `locale` cookie read by `App\Http\Middleware\SetLocale`; the admin panel (and Livewire requests) always stays in English. React pages translate with `useTrans()` (`resources/js/hooks/use-trans.ts`): `t('English wording', { name })`, and `tk('…')` marks wording defined outside components. Translations are `lang/{code}.json`, keyed by the English text; `LanguageTest` fails if a marked phrase is missing from a language or its `:placeholders` differ. Staff, supplier, settings and legal pages, product data and emails are English only. The Twi, Gã and Eʋegbe wording are drafts awaiting a native speaker's review.
- Motion: page changes use the View Transitions API through Inertia (`defaults.visitOptions` in `app.tsx`; skipped for in-page updates); `.enter` / `.enter-pop` with a `--i` index stagger items in (`resources/css/app.css`), and everything respects `prefers-reduced-motion`. The admin runs in Filament SPA mode with prefetching, so `tour.ts` restarts on `livewire:navigated`.
- Checks: `composer test` runs Pint, Larastan (level 7) and PHPUnit; `npm run check` and `npm run types:check` cover the frontend. `composer ci:check` runs everything, as CI does.

## What this is

Yande Gadgets is a bespoke Laravel 6 web application for a second-hand electronics retailer in Legon Boundary, Accra, Ghana. The shop (started 2011, named from the family name Nyande) buys used phones, appliances, TVs and audio, computers, gaming gear, cameras, and smart tech in the United Kingdom, ships them to Ghana, and resells them from a flagship store. In-store price negotiation is a selling point, so the website does not sell products outright. Customers browse what has arrived and pay a Stripe deposit to request an item the shop does not yet have. Staff record the real sale in store.

It was built in 2019–2020 as a final-year project (CS3010, BSc Computer Science for Business, May 2020) by Richard Nyande, supervised by Sylvia Wong. The client is the owner of the shop (the author’s father). Repo: https://github.com/nyander/Yandegadget (branch `master`).

The problem it replaces: everything was written in a notebook. Purchases, conditions, sale dates, and shipments were manual. There was no confirmation that a shipment arrived, customers could not see what was in the Accra store or request a missing product, and weekly reports omitted rent, wages, and shipping, so buying decisions were made on bad numbers. Profit margin had fallen from about 14% to 9% around 2016/17, and return on investment from about 10% to 5%. The shop also had no website while competitors (Gadget & Gears, Zoobashop) were online.

Goals: better record-keeping, less wasted buying, higher sales, and reports the owner can trust. PayPal was rejected because it was not available in Ghana. Stripe was used for request deposits. Off-the-shelf ERP (Oracle NetSuite) was rejected as too expensive and a poor fit for this UK-to-Ghana flow.

## Stack

- PHP ^7.2, Laravel ^6.2, MVC, Eloquent, Blade.
- Auth: `laravel/ui` 1.0 with email verification (`MustVerifyEmail`). New users are attached to the `customer` role in `RegisterController`.
- Frontend: Bootstrap 4, jQuery, Sass, Laravel Mix 4. React 16 is in `package.json` but the UI is Blade. Theme uses Ghana-flag colours (green, gold, red). Designs were done in Adobe XD. About 60 Blade views under `resources/views`.
- Database: MySQL in the design (the report also used SQLite locally via `.env`). Migrations live in `database/migrations` (21 files, dated 2019–2020).
- Payments: `cartalyst/stripe-laravel` ^11. Checkout uses Stripe.js; card data is tokenised and never stored in the app. The report used a Stripe test key.
- Cart: `hardevine/shoppingcart` ^2.7. Two cart instances: `default` (current shipment) and `saveForLater`.
- Notifications: Laravel database notifications (`notifications` table). Five classes in `app/Notifications`. `laravel-echo`, `pusher-js`, and `beyondcode/laravel-websockets` are dependencies; the working path in the code is database notifications shown in the navbar, not a confirmed live websocket flow.
- Other: `laravelcollective/html` for forms. Local dev in the report was Composer, VS Code, XAMPP, DB Browser for SQLite. GitHub was the backup.

Routes are almost all `Route::resource` declarations in `routes/web.php`. `routes/api.php` is the Laravel stub. There is no product search despite a search icon in the navbar.

## Roles and access

Four roles, seeded in this order so these IDs are assumed all over the code:

| ID | Role | Who |
|---|---|---|
| 1 | admin | The shop owner |
| 2 | staff | Store staff in Ghana |
| 3 | customer | Anyone who registers |
| 4 | supplier | UK sellers who offer stock |

Many-to-many via `roles` and `role_user`. Helpers: `User::hasRole`, `User::hasAnyRoles`. Gates in `app/Providers/AuthServiceProvider.php`:

- `admin-role` — admin
- `staff-role` — staff
- `supplier-role` — supplier (gate passes an array into `hasRole`, which expects a string; `hasAnyRoles` is the method that accepts a list)
- `customer-role` — same pattern as supplier
- `managing-requests` — customer or admin
- `request-products` — customer or admin
- `manage-shipped-products` — admin or staff
- `supproducts` — admin or supplier

Admin user management is `Route::namespace('Admin')->prefix('admin')->name('admin.')->middleware('can:admin-role')` → `UsersController` (index, edit, update, destroy only). Edit syncs roles. Destroy detaches roles then deletes the user. Settings page (`/settings`) is admin-only and is the hub for users, conditions, categories, suppliers, ship companies, wages, and the conversion rate.

Several controllers also call `Gate::denies(...)` and redirect to the product index. Some of those calls pass an array (`Gate::denies(['manage-shipped-products'])`), which is not how Laravel’s `denies` is meant to be called.

Navbar (`resources/views/inc/navbar.blade.php`) branches on `role_id` 1 and 2 with raw queries. Logged-in menu: Dashboard, and by gate: Upload Product, Requested Products, Reports, Shipped Product Management, Settings. Notification badge uses `unreadNotifications`.

## Business rules that matter

- Each physical item is its own `products` row. There is no quantity. Two iPhones in different conditions are two records. Bulk stock was left as future work.
- Money on products is stored in GBP (£). The sale screen converts to Ghana cedis with `conversions` row id 1: `GHS = selling_Price * rate`. The negotiated price cannot go below 70% of that GHS figure (`ghanaconversion - 30%`). If it does, the sale is rejected. If it is accepted, the entered GHS price is divided by the rate and written back into `selling_Price` as GBP, `sold` is set true, and `sold_Date` is stored. Future dates are blocked on date fields.
- Customers are not meant to see items that have not been received in Ghana, or items already sold. The product index query does not filter those; `resources/views/products/index.blade.php` hides or changes the card based on `received` and `sold`. Default listing is `featured = true` unless `?category=` is set. Sort: `?sort=low_high` or `high_low` on `selling_Price`. Pagination is 9.
- A customer request is not a purchase. They pay a deposit that depends on the condition. The shop later marks it acquired, then creates a real product.
- Suppliers list items they want to sell to the shop. The admin buys one, then promotes it into the main `products` table.
- Online checkout is the deposit only. The real sale is recorded by staff in the store so the price can still be negotiated.

## Data model

About 16 domain tables. Several foreign keys in `products`, `ships`, and `shipped_product` are commented out, so the database does not enforce those links. Eloquent relationships exist on the models anyway. `product_requests.acquired` is written as `->dafault('0')` in the migration (typo), so that default may never have been applied.

**users** — name, email, password, email_verified_at. **roles** / **role_user**.

**categories** — `type`, unique. Seeded: 1 Phones, 2 Appliances, 3 TV & Audio, 4 Computing, 5 Gaming, 6 Cameras, 7 Smart Tech. Reports count sales by these hard-coded ids.

**conditions** — `details` (A–D style label), `explanation`, `deposit` (amount the customer pays to request an item in that condition). Admin CRUD.

**suppliers** — name, address, contact, and `supplier_id` (the user id of the supplier account, despite the name). A user with the supplier role still has to add this row before they can upload products. Home view checks that link.

**products** — the shop’s stock.
- `user_id` who uploaded it
- `name`, `type` (category id), `cost` (GBP), `supplier` (supplier id)
- `purchase_Date`, `condition` (condition id), `condition_Notes`
- `selling_Price` (GBP), `received` bool, `sold` bool, `sold_Date`
- `requested_by` (user id, when it came from a customer request)
- `featured` bool, `thumbnail_path`
- Relations: belongsTo supplier, category, user, condition; hasMany images

**images** — `path`, `product_id` and/or `supplierproduct_id`. Files stored in `public/gallery`. Filenames get a timestamp so duplicates do not collide. Product pages use a carousel; the thumbnail is the active first slide.

**product_requests** — customer requests.
- `customer_id`, `name`, `type`, `condition`, `deposit`, `deposit_paid` (default false), `acquired`
- Customer sees only their own rows. Admin sees all.

**supplier_products** — stock a supplier is offering the shop.
- `supplier_id`, name, type, condition, condition_Notes, selling_Price, `purchased` bool, thumbnail_path

**ship_companies** — name, address, postcode, number, extra_information.

**ships** — one confirmed shipment.
- `user_id` (admin who confirmed), `shipped`, `shipment_company` (stored as a string, FK commented out), `shipment_date`, `shipment_cost`, `shipment_notes`, `received`

**shipped_product** — pivot of a shipment to products. `shipment_id`, `product_id`, `received`. FKs commented out. Model class is `App\ShippedProduct`.

**transactions** — manual ledger lines: `date`, `description`, `type` (FK to transaction_types), `amount`.

**transaction_types** — seeded labels used by the balance sheet. Ids are hard-coded in `ReportController`. Important mapping:

| ID | Label | Used as |
|---|---|---|
| 1 | cash | current asset |
| 2 | Account Recievable | current asset |
| 3 | Inventory | not summed; stock is computed from unsold products |
| 4 | Prepaid | current asset |
| 5 | Other Current Assets | current asset |
| 6 | Rent | current liability |
| 7 | Land | fixed asset |
| 8 | Building | fixed asset |
| 9 | Vehicles | fixed asset |
| 10 | Machinery | fixed asset |
| 11 | Computer/Software | fixed asset |
| 12 | Other Fixed Assets | fixed asset |
| 13 | Accounts Payable | liability and also folded into income-statement expenses |
| 14 | Overdrafts | current liability |
| 15 | Current Lease Payaple | current liability (spelling is in the seeder) |
| 16 | Customer repayments | current liability |
| 17 | Other Current Assets | labelled that way in the seeder, summed as a current liability |
| 18 | Long-Term Depts | long-term liability |
| 19 | Pension Fund Liability | long-term liability |
| 20 | Preffered Taxed Liability | long-term liability |
| 21 | Other long term liabilities | long-term liability |
| 22 | Retained Equity | seeded, but retained earnings in the report come from sold products’ selling prices, not this type |
| 23 | Treasury Stock | equity |
| 24 | Common Stock | equity |
| 25 | Other Shareholder Equity | equity |
| 26 | Other Income | income statement |
| 27 | Income before Provision for income taxes | income statement |
| 28 | Provision for Income taxes | income statement |

**staff_wages** — `startDate`, `endDate`, `wage` (treated as a daily rate). Report code prorates overlapping wage periods into the selected date range.

**conversions** — a single `rate` (GBP to GHS). Code always reads `Conversion::find(1)`. At the time of the project the rate was about 7 cedis to the pound. Admin edits it from settings.

**notifications** — Laravel default.

## Features, in the order they were built

### 1. Auth
`Auth::routes(['verify' => true])`. Register, login, password reset, email verification. Controllers that need a logged-in verified user use `middleware(['auth','verified'])`. Views extend `layouts/app.blade.php`.

### 2. Public site
- `GET /` → `PagesController@index` — “Welcome To Yande Gadgets”.
- `GET /about` — history, map embed for Legon, address “27 Ayawaso, Legon Boundary, Accra, Ghana”.
- `GET /home` — dashboard (`HomeController`). Auth required. The report describes a supplier dashboard (counts of uploaded and sold products, and a prompt to add supplier details). The current `HomeController@index` only returns `view('home')`; that supplier logic may live in the view or may have been simplified.
- `GET /settings` — admin only.

### 3. Product management (`ProductsController`, `/products`)
Admin only for create, edit, update, destroy.

- **index** — featured products, or one category, optional price sort, 9 per page.
- **create / store** — name, cost, category, purchase date, supplier, condition, notes, selling price, featured, thumbnail plus extra images. Images go to `public/gallery`. Validation rejects non-images.
- **show** — gallery and details. Received vs not-received changes what the visitor sees.
- **edit / update** — same fields. The edit Blade uses `=` where a comparison is needed (`@if($product->featured = true)`), so the “featured” radio can display the wrong state.
- **destroy** — deletes the product and its images.
- **received** — `GET /products/received/{id}`. Marks that product received.
- **purchase / purchaseupdate** — `GET/PUT /products/{id}/purchase`. Staff sale screen. Applies the 30% GHS floor and writes the sale back in GBP.
- **storesupproduct** — `GET /supplierproducts/{id}/addproduct`. Sets `supplier_products.purchased = true` and opens a prefilled “add to main catalogue” form (`products/storeSupplierProduct`).
- **storereqproduct** — `GET /prodrequest/{id}/addproduct`. Sets `product_requests.acquired = true`, notifies the customer with `ProductAcquired`, opens `products/storeRequestedProduct` prefilled with the request.
- **MarkRead** — `GET products/notifications/{id}`.

### 4. Images (`ImageController`, `/images`)
Edit, update, destroy. `PUT /images/updatesuppliers/{id}` updates a supplier product’s images.

### 5. Shipment
Admin builds a cart, confirms it, staff in Ghana confirm arrival.

- `ShipmentController` `/shipments`: index shows the cart; `store` adds a product if it is not already in the cart; `destroy` removes one line; `switchToSaveForLater` moves a line to the `saveForLater` cart instance.
- `SaveForLaterController` `/saveForLater`: `switchToShipment` moves a line back; `destroy` removes a line. `GET /empty` wipes the save-for-later cart.
- `ShipmentConfirmationController` `/confirmations`: form for ship company, date, cost, notes. `store` calls `addToOrdersTable` (creates a `ships` row with `shipped = true`, then one `shipped_product` row per cart line), empties the default cart, and notifies every user with role id 2 via `NewShipment`.
- `ShipController` `/ships`: admin and staff. Index lists shipments. Show lists products on that shipment. `GET /ships/received/{id}` sets that shipment’s `received` flag, marks the first unread notification read, and notifies every admin (role id 1) with `ShipmentReceived`.
- Per-product receipt is `ProductsController@received`, which sets `received` on the product. The report says a shipment row turns green once every product in it is received, and customers can then see the product.
- `ShipCompanyController` `/shipcompanies`: admin CRUD for carriers.

### 6. In-store sale
Covered under product `purchase` / `purchaseupdate`. Staff open a received product, see the GHS price and the minimum allowed price, enter the negotiated price and the sale date, and submit. Sold products are treated as no longer for sale in the product cards.

### 7. Customer requests and Stripe (`ProductRequestController`, `CheckoutController`)
- Create: name, category (only categories the shop sells), condition. Deposit is filled by JavaScript from the selected condition and is read-only.
- Store: writes `product_requests` with `deposit_paid = false`.
- Index: admin sees every request; a customer sees their own (`managing-requests` gate). Acquired rows are shown in green.
- Customer clicks Pay → `CheckoutController@show` → `checkouts/index`. Billing fields plus Stripe card element. `update` validates name, email, address, city, postcode, phone, name on card, creates a Stripe charge for `amount = request->charge`, sets `deposit_paid = true`, and notifies admins with `NewRequestedProduct`. Card errors (invalid or expired) send the user back with the Stripe message. The submit button is disabled after one click so a double submit cannot charge twice.
- `CheckoutController::proceed` is an empty method.
- When the shop has found the item, admin clicks Acquired → `storereqproduct` → customer gets `ProductAcquired` → admin completes the real product form.

Conditions (labels, explanations, deposit amounts) are admin CRUD via `ConditionsController` `/conditions`.

### 8. Suppliers (`SuppliersController`, `SupplierProductController`)
- Supplier must have a `suppliers` row tied to their user before uploading.
- Supplier product CRUD mirrors shop products (name, category, condition, notes, price, images) but is a separate table.
- Index: admin sees all offers; a supplier sees only their own (`supproducts` gate).
- On upload, admins get `NewSupplierProduct`. `MarkRead` at `supproduct/notifications/{id}`.
- Admin “Purchase” runs `storesupproduct`, flags `purchased`, and copies the offer into the main catalogue.
- The supplier index also links out to the second-hand sites the owner already uses, for when the offer list does not have the requested item.

### 9. Transactions and wages
- `TransactionController` `/transactions`: admin records one or many lines in one go (date, description, type, amount). Index lists them. Destroy deletes a line. Product cost and shipment cost are not re-typed here; reports read those from `products` and `ships`.
- `StaffWageController` `/staffwages`: amount plus start and end date. Used as a daily rate when a report date range overlaps the wage period.

### 10. Reports (`ReportController` `/reports`, admin only)
- **index** — rolling 7 months of new user counts and sold-product counts, plus this month’s transactions.
- **create / store** — start and end date (end must be on or after start). Produces:
  - units sold per category id 1–7 and per condition id 1–3
  - a balance sheet: assets from transaction types plus unsold stock (`sum of selling_Price` where `sold = false` and `created_at` in range); liabilities include shipment cost in range, prorated wages, and the liability transaction types; equity includes sold products’ selling prices as retained earnings
  - an income statement: sales (sold `selling_Price`) minus cost of those products = gross profit; expenses = delivery + prorated wages + accounts payable + rent + overdrafts + lease + customer repayments; plus other income and tax-provision types
- The report does not colour negative figures. That was listed as future work.
- Category and condition breakdowns assume the seeded ids. Adding a category does not add a report column.

### 11. Conversion rate (`ConversionController` `/conversions`)
Index, edit, update. Always row id 1. Changing it changes every future GHS sale screen. It does not rewrite prices already stored.

### 12. Categories
`CategoriesController` `/categories`. Admin CRUD. `type` is unique.

## Notifications

| Class | When | Who |
|---|---|---|
| `NewShipment` | Shipment confirmed | role id 2 (staff) |
| `ShipmentReceived` | Shipment marked received | role id 1 (admin) |
| `NewRequestedProduct` | Deposit paid | admin |
| `ProductAcquired` | Request marked acquired | that customer |
| `NewSupplierProduct` | Supplier uploads an offer | admin |

Navbar renders unread vs read differently for staff and admin (green vs grey cards). Staff notification links go to `/ships`.

## Main routes (non-resource extras)

```
GET  /                                      pages index
GET  /about
GET  /settings                              admin
GET  /home                                  auth
Auth::routes verify
resource /products
GET  products/notifications/{id}            MarkRead
GET  /products/received/{id}                products.received
GET  /products/{id}/purchase                products.purchase
PUT  /products/{id}/purchase                products.purchaseupdate
GET  /supplierproducts/{id}/addproduct      products.storesupproduct
GET  /prodrequest/{id}/addproduct           products.storereqproduct
resource /images
PUT  /images/updatesuppliers/{id}
resource /supplierproducts
GET  supproduct/notifications/{id}
resource /requests                          ProductRequestController
resource /staffwages
resource /conditions
resource /categories
resource /suppliers
resource /shipcompanies
resource /confirmations                     ShipmentConfirmationController
resource /checkouts                         CheckoutController
resource /ships
GET  /ships/received/{id}
resource /reports
resource /transactions
resource /shipments
POST /shipments/switchToSaveForLater/{product}
resource /saveForLater
POST /saveForLater/switchToShipment/{product}
GET  /empty                                 destroys saveForLater cart
resource /conversions
prefix admin, can:admin-role, resource /users except show, create, store
```

## Code quirks worth knowing before changing anything

- Laravel 6 and PHP 7.2. Do not assume modern Laravel APIs, typed properties, or the current `Route::` controller syntax. Controllers are referenced as strings (`'ProductsController@index'`).
- Foreign keys on products (type, supplier, condition, requested_by), ships.shipment_company, and shipped_product are commented out. Deletes can orphan rows.
- `product_requests` migration uses `dafault` instead of `default` on `acquired`.
- `products/edit.blade.php` and `products/show.blade.php` assign `featured = true` inside `@if`.
- `CheckoutController::proceed` is empty.
- Role checks often use raw `role_id` 1, 2, or 3 instead of the gate. Reordering the roles seeder would break notifications.
- `supplier-role` and `customer-role` pass an array to `hasRole`.
- `ShipController@received` marks only `unreadNotifications[0]` as read and flips the whole shipment, separately from per-product `ProductsController@received`.
- `Image` and `product_requests.customer_id` use `unsignedInteger` while parent keys are `bigIncrements`.
- Reports hard-code category ids 1–7, condition ids 1–3, and transaction type ids. Stock and retained earnings are derived from product rows, not from the Inventory or Retained Equity transaction types.
- Wage math treats `wage` as a daily amount and sums `diffInDays` across overlapping periods. There is also an unused `$staff_payable = 5 * $diff_in_days` in the same method.
- The navbar search icon does nothing.
- No stock quantity.
- No staff photo upload to prove a shipment arrived.
- Negative report numbers are not highlighted.
- `storesupproduct` loads users with `role_id` 3 (customers) into the supplier-purchase form. That looks unintentional.
- Public product index is “featured” unless a category is chosen, and visibility of unreceived or sold items is handled in the Blade, not in the query.

## What “done” meant in 2020

The owner’s feedback, from the report: the process is better, staff duties are visible, and the reports were “the icing on the cake.” Explicitly still open: quantity for bulk buying, staff photos on receipt, product search, and colour on negative income-statement and balance-sheet figures.

When changing this app, keep the UK-buy / Ghana-sell split, GBP storage with a single GHS rate, the 30% negotiation floor, deposits via Stripe rather than full online checkout, and one database row per physical item.
