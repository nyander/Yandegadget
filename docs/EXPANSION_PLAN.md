# Expansion plan: clothing and bulk importing

Notes for future development, written November 2026. Nothing here is built yet.

The shop may grow in two directions:

1. **Selling other kinds of products**, starting with clothing.
2. **Buying in bulk from overseas platforms** such as Alibaba, for both electronics and clothes, alongside the current UK buying.

The app's building blocks (products, stock, shipments, sales, requests, reports, the ledger) all carry over. What has to change are the parts that assume second-hand electronics, bought one at a time in the UK.

## What stays as it is

- Buying list, supplier offers, shipments, Track, staff check-in with photos.
- Sales at the till, customer requests and deposits, Paystack.
- The ledger, the income statement and the balance sheet, and the dashboard reports. They already work by category.
- People, roles, languages, the owner's guide and the page tours.

## What assumes electronics today

| Today                                                                    | Where in the code                                         | Problem for clothing or bulk imports                                                                          |
| ------------------------------------------------------------------------ | --------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------- |
| Every physical item is its own `StockUnit` (grade, cost, price, serial). | `app/Models/StockUnit.php`, `StockIntake`, "Add several"  | Fine for unique second-hand pieces; clumsy for 200 identical new items in sizes and colours.                  |
| One shared list of condition grades, each with a request deposit.        | `conditions` table, `Condition`                           | Clothes need their own grades (New with tags, Like new, Good); new stock is all "New".                        |
| Products have no category-specific details.                              | `Product`, `ProductForm`                                  | Clothing needs size, colour, material and fit; electronics need storage, model and IMEI.                      |
| One shop-wide haggling limit (70%).                                      | `ShopSettings::NEGOTIATION_FLOOR`, `SaleService`          | Clothes may be fixed-price, or need a different limit.                                                        |
| Costs are in pounds, with one GBP → GHS rate in use.                     | `config('shop.list_currency')`, `Pricing`, `ExchangeRate` | Alibaba is mostly USD, sometimes CNY.                                                                         |
| An item's cost is its purchase price; shipping is a separate expense.    | `SaleFigures`, `IncomeStatementReport`                    | Freight, customs duty, clearing fees and port charges can add 30–60%. Margins look much better than they are. |
| Items are recorded after they're bought.                                 | Stock units, supplier offers                              | Bulk buying has an ordering stage: deposit, production, balance, shipping.                                    |
| Shipments start from the UK base.                                        | `LocationType::Buying`, `ShipmentForm`                    | Imports ship from a supplier or forwarder in China straight to Accra, by sea or air.                          |
| Buying suggestions aim for 2 months of stock.                            | `BuyingInsights::MONTHS_OF_COVER`                         | Long lead times and minimum order quantities change when and how much to reorder.                             |

## The changes

### 1. Departments, grades and category details

- **Departments**, such as Electronics and Clothing, above categories. The shop's navigation and the reports can group by them.
- **A set of condition grades per department**, each grade with its own request deposit. Add a "New" grade for new stock.
- **Category-specific fields** (size, colour, material, storage, model…) defined per category. They drive the "Add item" and "Add several" forms, filters on the shop (for example "Size M", "Black"), and customer requests ("a size M denim jacket").

### 2. Two ways of tracking stock

Chosen per product:

- **Track each item**: one record per physical item, as now. For second-hand stock, where every piece differs.
- **Track a quantity**: **variants** (size, colour, model) with a **stock count** each. The till sells "1 × Medium, black" and takes it off the count.

For new phones bought in bulk, use quantity tracking but **record the IMEI or serial when the item is sold**, for warranty and registration.

### 3. More currencies and suppliers abroad

- Costs in **USD and CNY** as well as GBP. Money already stores a currency with every amount (`MoneyCast`). The work is exchange rates per currency, and converting each cost at the rate on its day, as the reports already do for pounds.
- **Suppliers outside the UK**, and new **starting points** for shipments (a supplier or freight forwarder abroad).

### 4. Purchase orders

A **purchase order** for each bulk order:

- The supplier, and lines with product or variant, quantity, unit price and minimum order quantity.
- Payments to the supplier (often a 30% deposit, then the balance) and the amount still owed.
- The expected date, and the shipment or shipments it arrives on.
- **Receiving against the order**: ordered 200, received 196, 3 faulty. Shortages and faulty items feed a claim to the supplier, for example through Alibaba Trade Assurance, and the supplier's track record.

On the balance sheet, deposits paid on orders that haven't arrived show as **money paid to suppliers in advance**, and stock that's been paid for and is on its way as **goods in transit**.

### 5. Landed cost (most important for the business)

Each shipment records its **freight, Ghana customs duty and levies, clearing agent fees, port charges and local transport**, with the documents attached: bill of lading, supplier invoice and customs declaration (kept for GRA).

These costs are **spread across the items received**, by value, quantity or weight. Every item then carries its true cost, and gross profit, the "What sold" margins and the buying suggestions are all based on real figures.

This should be in place **before the first big import arrives**, so its margins are right from the start.

### 6. Smarter reordering

Buying suggestions take each supplier's **lead time** and **minimum order quantity** into account. For example: reorder now, because sea freight takes six weeks and you'll run out in four.

### 7. Optional, later

- **Bulk lots** (bales of second-hand clothes): buy a lot at one price, split it into items as they're sorted, share the cost, and record unsellable pieces.
- **Returns and exchanges at the till** (clothes that don't fit), putting the item back into stock. This is separate from voiding a sale.
- **Wholesale price tiers** for traders buying in quantity (for example a lower price for 10 or more).
- A **CSV import** of order lines. Alibaba's APIs are mostly for sellers, so plan on entering orders by hand or importing a spreadsheet, not a live link.
- A **broader name and branding** than "Yande Gadgets".

## Suggested order

1. Departments, a set of grades per department (including "New"), category fields.
2. Variants and quantity stock, keeping "track each item" for second-hand stock.
3. USD and CNY costs and rates, and suppliers and starting points abroad.
4. Purchase orders.
5. Landed cost.
6. Reordering by lead time and minimum order; then the optional items as needed.

Steps 1–3 are enough to start selling second-hand clothes and new electronics. Steps 4–5 are what make bulk importing work, and should come before the first large order.

## Decisions to make first

- Start with **second-hand clothes** (unique pieces: step 1 is enough) or **new clothes in sizes and quantities** (steps 1–3)?
- Which **suppliers and currencies** will be used first, and do imports go **straight to Accra** or through the UK?
- Is the **haggling limit** the same for clothes, or should they be fixed-price?
- How should landed costs be shared: **by value, by quantity or by weight**?
- Will the shop **sell wholesale** to other traders?
- Does the business **name** change?
