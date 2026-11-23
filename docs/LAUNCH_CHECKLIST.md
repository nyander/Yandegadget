# Launch checklist

Work through this before telling customers about the site. Items marked **owner** need a decision or an account from the shop.

## Decisions and accounts (owner)

- [x] Hosting: Forge + VPS (see `docs/DEPLOYMENT.md`).
- [ ] Domain name, and who holds the registrar account.
- [ ] Resend account (decided) with the domain verified (DNS records from Resend), and an API key in `RESEND_API_KEY`.
- [ ] Paystack business account verified and switched to live.
- [ ] Branding: logo and colours. The Ghana-flag colours in `resources/css/app.css` are placeholders.
- [ ] Deposit amount for each condition grade, under Catalogue → Conditions (currently the 2020 values).
- [x] Refund rules: built. Check the cancellation fee (25%) and collection window (14 days) under Shop settings.
- [ ] Any 2020 records to bring across. None are in the repository.

## Legal (owner)

- [ ] Register the shop with the Data Protection Commission as a data controller, and set `SHOP_DPC_REGISTRATION`.
- [ ] Have someone qualified read the privacy policy (`/privacy`) and terms (`/terms`). They were written for this app but aren't legal advice.
- [ ] Set `SHOP_CONTACT_EMAIL` and `SHOP_CONTACT_PHONE` so people can reach you about their data.

## Server

- [ ] Production `.env` filled in (`APP_DEBUG=false`, HTTPS `APP_URL`, `SESSION_SECURE_COOKIE=true`).
- [ ] First deploy steps run, `shop:create-admin` done, owner can sign in with two-factor turned on.
- [ ] Queue worker running: invite a test user and check the email arrives.
- [ ] Scheduler cron added; `php artisan schedule:list` shows the backup jobs.
- [ ] `php artisan backup:run` succeeds, a copy lands in the off-site bucket, and a **test restore** works.
- [ ] Sentry DSN set; trigger a test with `php artisan sentry:test`.
- [ ] Uptime monitor on `https://<domain>/up`.
- [ ] Paystack webhook URL set; one live deposit paid and refunded end to end.
- [ ] `https://<domain>/robots.txt` and `/sitemap.xml` load; submit the sitemap in Google Search Console.

## Data

- [ ] Exchange rate entered.
- [ ] Categories and conditions checked.
- [ ] Carriers you actually use added under Shipping → Carriers.
- [ ] Staff accounts created, each with their store.
- [ ] Opening ledger balances entered (cash, capital, any loans), so the balance sheet makes sense.
- [ ] Staff wages entered.

## Soft launch (one to two weeks)

- [ ] Enter the stock already in the Accra store as stock units with status _In store_, with photos.
- [ ] Owner works through the **Getting started** checklist on the dashboard and reads the **Guide** in the admin panel.
- [ ] Train staff with `docs/STAFF_GUIDE.md`: one shipment check-in and three or four sales each, on the real site.
- [ ] Share the link with family, friends and regular customers only. Ask them to browse on their phones and make one request.
- [ ] Each evening, compare the day's sales screen with the till or notebook.
- [ ] Fix what comes up, then announce the site (WhatsApp status, Instagram, a sign in the shop).
