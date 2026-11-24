## Building a shipment

1. Go to **Shipping → Shipments** and click **New shipment**. Choose where it's going from and to (normally the UK base to the Legon store).
2. Open it and use **Add items** to pick the items going in the boxes. Only items that are _Purchased (UK)_ can be added.
3. Fill in the carrier, tracking number and the shipping cost in pounds. The cost goes into the reports as an expense.

You can add and remove items until you dispatch it.

## Dispatching

When it leaves, click **Dispatch** and enter the date. The items become _In transit_ and the store staff are told it's on its way.

## Tracking

Once it's on its way, **Track** (on the shipment, and on its row in the list) opens the carrier's tracking page for this shipment. Staff see the same link on their tablet. It works when the carrier has a tracking page set up (below) and the shipment has a tracking number.

## When it arrives

Staff check each item in on their tablet: arrived OK, damaged (with photos and a note), or missing. You get a notification when they're done.

- Items marked **arrived** or **damaged** go into the store and onto the website.
- **Missing** items stay _In transit_, so you can chase the carrier. If one never turns up, mark it _Written off_.
- If an item had been found for a customer's request, it's reserved for them automatically and they're told it has arrived.

## Carriers

Keep your shipping companies under **Shipping → Carriers**, with their tracking page. Copy the address of the carrier's tracking page for one parcel and put `{number}` where the tracking number goes, for example `https://www.dhl.com/gh-en/home/tracking.html?tracking-id={number}`. Without `{number}`, **Track** opens the carrier's page and you type the number in yourself.
