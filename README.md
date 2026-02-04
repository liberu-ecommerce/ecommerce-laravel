# Liberu Ecommerce

[![Install](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/install.yml/badge.svg)](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/install.yml)
[![Tests](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/tests.yml)
[![Docker](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/main.yml/badge.svg)](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/main.yml)
[![Codecov](https://codecov.io/gh/liberu-ecommerce/ecommerce-laravel/branch/main/graph/badge.svg)](https://codecov.io/gh/liberu-ecommerce/ecommerce-laravel)

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A modular, production-ready e-commerce platform built with Laravel, Livewire and Filament. Designed for extensibility and fast developer onboarding.

- PHP · Laravel · Livewire · Filament

---

## Quick start (local)

Requirements: PHP 8.3+, Composer, a database (MySQL / MariaDB / Postgres). Docker is optional.

1. Clone and install

   ```bash
   git clone https://github.com/liberu-ecommerce/ecommerce-laravel.git
   cd ecommerce-laravel
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

2. Configure `.env`
   - Update database settings.
   - Add payment and dropshipping keys (example below for Stripe and DropXL):

   ```
   STRIPE_KEY=pk_test_xxx
   STRIPE_SECRET=sk_test_xxx
   STRIPE_WEBHOOK_SECRET=whsec_xxx
   DROPXL_API_KEY=Bearer xxx
   DROPXL_API_URL=https://api.dropxl.com
   ```

3. Migrate and seed

   ```bash
   php artisan migrate --seed
   ```

4. Serve locally

   ```bash
   php -S 127.0.0.1:8000 -t public
   # or use Sail/Docker if you prefer
   # ./vendor/bin/sail up -d
   ```

---

## Stripe setup and testing

This project uses a server-side Stripe integration via `stripe/stripe-php`. To enable:

1. Add keys to `.env` (see above).
2. Use Stripe test cards in checkout (example):
   - Card number: 4242 4242 4242 4242
   - Any future expiry, any CVC, any ZIP
3. Webhooks: if you use a local webhook helper (stripe CLI) set `STRIPE_WEBHOOK_SECRET` and configure webhooks to point to `/stripe/webhook` if enabled.

---

## Dropshipping (DropXL)

DropXL integration is configurable via `config/dropshipping.php` and expects `DROPXL_API_KEY` and `DROPXL_API_URL` in `.env`.

On checkout, check "Ship directly to recipient (Drop shipping)" to select a supplier (DropXL is available out of the box). Supplier order placement happens after a successful payment and `orders` will include `supplier_id` and `supplier_reference`.

For local testing, point `DROPXL_API_URL` to a mock endpoint and return a success JSON to avoid hitting production APIs.

---

## Additional setup notes

- After pulling the latest changes, run `composer install` to install new dependencies (including Stripe PHP SDK):

```bash
composer install
```

- If you rely on queued supplier placement (recommended), run a queue worker locally:

```bash
php artisan queue:work --tries=3
```

(or use `php artisan queue:listen` / Horizon if configured)

---

## Quick smoke-tests

Stripe (checkout flow)
1. Ensure `.env` contains STRIPE_KEY and STRIPE_SECRET.
2. Start the app and queue worker (if using queued supplier placement).
3. Add a product to cart and go through the normal checkout flow (use the full checkout page in the app).
4. Choose Stripe as payment method and enter test card: 4242 4242 4242 4242. Complete checkout.
5. Expected: payment succeeds, order status becomes `paid`, supplier job queued if dropshipping selected.

DropXL (dropshipping)
1. Set `DROPXL_API_URL` to a mock endpoint (or real DropXL credentials if available).
2. For local mocks, return successful JSON:

```json
{ "success": true, "data": { "id": "dropxl-123", "reference": "DLX-123" } }
```

3. Complete a checkout using the Drop shipping option. Ensure `orders.supplier_id` and `orders.supplier_reference` are set after the queued job runs.

---

## Troubleshooting

- If orders are stuck with `supplier_queued`, verify the queue worker is running and check `storage/logs/laravel.log` for job errors.
- If Stripe charges fail: validate `STRIPE_SECRET` in `.env`, confirm the publishable key is present in `config/services.php`, and check the logs for Stripe API errors.

---

## What’s included

- Livewire cart & checkout components
- Shipping methods with server-side calculation and drop-shipping premium
- Payment gateway factory with Stripe and PayPal implementations
- Dropshipping service with supplier transformation for DropXL
- Order persistence and order item creation

---

## Related projects

| Project | Repository |
|---|---|
| Accounting | https://github.com/liberu-accounting/accounting-laravel |
| Automation | https://github.com/liberu-automation/automation-laravel |
| Billing | https://github.com/liberu-billing/billing-laravel |
| Boilerplate | https://github.com/liberusoftware/boilerplate |
| CMS | https://github.com/liberu-cms/cms-laravel |
| Control Panel | https://github.com/liberu-control-panel/control-panel-laravel |
| CRM | https://github.com/liberu-crm/crm-laravel |
| Ecommerce | https://github.com/liberu-ecommerce/ecommerce-laravel |
| Social Network | https://github.com/liberu-social-network/social-network-laravel |

---

## Contributing

Fork → create a focused branch → open a PR against `main`. Include tests for new behavior. CI runs on push; ensure `install` and `tests` workflows pass.

---

## License

MIT — see the `LICENSE` file.

If you'd like CLI commands, tests, or example API payloads added to the README, tell me which sections to expand.
