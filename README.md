# Yemi Adebanjo — Personal Brand & Digital Commerce Platform

Laravel 12 · Inertia.js 2 · React 19 + TypeScript · Tailwind CSS 4 · MySQL. Built from `yemi_adebanjo_prd.md`.

## Running it locally

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate   # set DB_* (MySQL)
php artisan migrate --seed                          # roles, defaults, owner account (+ sample content when APP_ENV=local)
php artisan storage:link
composer dev                                        # server, queue worker, logs, Vite
```

Sign in at `/login` as `owner@yemiadebanjo.test` / `password` (local only — set `OWNER_EMAIL` / `OWNER_PASSWORD` before seeding anywhere else). The dashboard is at `/admin`.

In production also run the scheduler (`php artisan schedule:run` every minute) and a queue worker. Those handle booking-hold expiry, reminders, scheduled articles and email.

## Brand

The colours come from the logo: deep green `#0B2C1D`, bronze-gold `#957546`, paper `#F7F7F7` (tokens in `resources/css/app.css`). The transparent logo files are in `public/images/brand/`.

## Integrations (pending until configured)

| Service | Env | Notes |
|---|---|---|
| Payments | `PAYMENT_GATEWAY=paystack`, `PAYSTACK_*` | Webhook URL: `/payments/webhook/paystack`. `test` is a local sandbox and is refused in production. |
| Email | `MAIL_*`, `ADMIN_NOTIFICATION_EMAIL` | Defaults to the `log` mailer. |
| Newsletter | `NEWSLETTER_PROVIDER=mailchimp`, `MAILCHIMP_*` | Until set, subscribers (double opt-in) are stored locally. |
| Analytics | `GA_MEASUREMENT_ID` | Loads only after the visitor consents. |

The Website Settings screen shows the live status of each one.

## Key rules enforced on the server

- Payment is verified with the provider before an order or booking is fulfilled, and it is applied only once.
- Prices are calculated on the server and copied into the order at purchase time.
- E-books are kept on a private disk and served only through expiring download links with a download limit.
- Bookings take a calendar lock and a unique slot index, so the same slot cannot be booked twice. Unpaid holds expire.
- Each admin route checks a permission. The roles are `owner`, `content-manager` and `operations-manager`.
- Awards need owner approval, and testimonials need recorded permission, before they appear publicly. Private events never appear.

## Tests

`php artisan test` runs against an in-memory SQLite database, so it never touches the main database.
