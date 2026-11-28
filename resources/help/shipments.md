## Building a shipment

1. Go to **Shipping → Shipments** and click **New shipment**. Choose where it's going from and to (normally the UK base to the Legon store).
2. Open it and use **Add items**. Pick **bulk purchases** (every item in them goes in at once, e.g. all 50 watches) and/or **single items**. Only items bought and not already on a shipment can be added. On the shipment, a bulk purchase shows as one group you can open.
3. Fill in the carrier, tracking number and the shipping cost in pounds. The cost goes into the reports as an expense.

You can add and remove items until you dispatch it.

## Straight from a supplier

If a supplier (e.g. on Alibaba) sends goods straight to Accra, not via the UK base, choose **From: Direct from supplier** on the shipment and pick the supplier. When you buy such stock, choose "the supplier ships straight to Accra" for where it is.

## Dispatching

When it leaves, click **Dispatch** (at the top, or at the bottom under the items) and enter the date. The items become _In transit_ and the store staff are told it's on its way.

## Tracking

Once it's on its way, **Track** (on the shipment, and on its row in the list) opens the carrier's tracking page for this shipment. Staff see the same link on their tablet. It works when the carrier has a tracking page set up (below) and the shipment has a tracking number.

## When it arrives

Staff check each item in on their tablet: arrived OK, damaged (with photos and a note), or missing. A bulk purchase shows as one card: **All 50 arrived** checks them all in at once; any damaged or missing ones are checked one by one first. You get a notification when they're done.

- Items marked **arrived** or **damaged** go into the store and onto the website.
- **Missing** items stay _In transit_, so you can chase the carrier. If one never turns up, mark it _Written off_.
- If an item had been found for a customer's request, it's reserved for them automatically and they're told it has arrived.

## Carriers

Keep your shipping companies under **Shipping → Carriers**, with their tracking page. Copy the address of the carrier's tracking page for one parcel and put `{number}` where the tracking number goes, for example `https://www.dhl.com/gh-en/home/tracking.html?tracking-id={number}`. Without `{number}`, **Track** opens the carrier's page and you type the number in yourself.
