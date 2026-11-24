# Using NYA — a guide for the shop

Short, practical notes for training. Staff use the main site on a tablet or phone; the owner also uses the admin panel at `/admin`.

## Signing in

The owner adds each person under **Settings → Users → Add a person**. They get an email to set their own password. Turn on two-factor sign-in or a passkey under **Settings → Security** — staff accounts can record sales, so they're worth protecting.

Forgotten password: use **Forgot password** on the login page.

---

## Staff: when a shipment arrives

1. Open **Shipments**. The shipment the owner sent shows as _In transit_.
2. Open it. Every item on it is listed with its reference (e.g. `YG-000123`) and description.
3. For each item in the boxes:
    - **Arrived OK** — it's here and as described. Add a photo or two if you can.
    - **Damaged** — it's here but not as described. Take photos and write what's wrong. It still goes into the store, with your note on it.
    - **Missing** — it isn't in the boxes. It stays _in transit_ so the owner can chase the carrier.
4. When every item has been checked, the shipment is marked received and the owner is told. Items you marked OK or damaged appear in the online shop straight away.

Tips: check serial numbers against the label when there is one; photos are your proof of the condition on arrival.

## Staff: selling an item

1. Open **Sell** and search by the item's reference, serial number or name.
2. The screen shows the **listed price** in cedis and the **lowest price you can accept**. You can't record a sale below that — if the customer wants to go lower, ask the owner.
3. If the item is **reserved** for a customer who paid a deposit, the screen says so and shows the deposit. Only sell it to that customer; the deposit comes off what they pay.
4. Enter the **agreed price**, the **payment** method (cash, mobile money, card or bank transfer) and, for MoMo or card, the transaction reference.
5. Customer name, phone and notes are optional. Ask before writing them down, and only if the customer is happy for us to keep them.
6. **Record sale**. You'll see a receipt you can print or show. The item disappears from the online shop.

Made a mistake? Don't record a second sale. Tell the owner, who can **void** the sale in the admin panel; that puts the item back in stock.

---

## Owner: the admin panel (`/admin`)

### Every day or two

- **Dashboard** — sales, profit, what's selling and how long stock has been sitting.
- **Sales → Customer requests** — new paid requests to look for. When you buy a matching item, use **Match** to link it; the customer is told automatically and the item is reserved for them.
- **Buying → Supplier offers** — accept or decline what UK suppliers have offered. Accepting creates the stock item ready to ship.

### When buying in the UK

- **Catalogue → Products / Stock units** — add each item you buy: its condition, what it cost (£) and the price to list it at (£).
- **Shipping → Shipments** — create a shipment, add the items, then **Dispatch** it with the carrier, cost and date. Staff in Accra are notified.

### When the rate moves

- **Settings → Exchange rates** — add the new GBP → GHS rate. Shop prices change straight away; past sales keep the rate they were sold at.

### Money

- **Finance → Ledger** — rent, bills, cash in the bank, loans, equipment: anything that isn't a sale, an item's cost or shipping (those are already counted). Use a minus amount to reduce a balance, e.g. paying off part of a loan.
- **Finance → Staff wages** — each person's wage and whether it's per day, week or month. Add an end date when it stops or changes, then add the new wage.
- **Reports → Income statement / Balance sheet** — pick the dates and download a PDF or CSV for the accountant. Red figures in brackets are negative.

### People

- **Settings → Users** — add staff and suppliers, change roles and stores.
- If a customer asks us to delete their data, open their account and use **Close account**. Their name and email are removed; sales and deposits stay without their name.
