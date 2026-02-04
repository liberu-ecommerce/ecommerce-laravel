# Liberu Ecommerce

[![Install](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/install.yml/badge.svg)](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/install.yml) [![Tests](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/tests.yml) [![Docker](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/main.yml/badge.svg)](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/main.yml) [![Codecov](https://codecov.io/gh/liberu-ecommerce/ecommerce-laravel/branch/main/graph/badge.svg)](https://codecov.io/gh/liberu-ecommerce/ecommerce-laravel)

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A modular, production-ready e-commerce platform built with Laravel, Livewire and Filament. Focused on extensibility, developer productivity, and practical features for online stores.

Key technologies: PHP · Laravel · Livewire · Filament

---

## Quick start (local)

Requirements: PHP 8.3+, Composer, a database (MySQL / MariaDB / Postgres). Docker is optional.

1. Clone repository

   git clone https://github.com/liberu-ecommerce/ecommerce-laravel.git

2. Install and prepare

   composer install
   cp .env.example .env
   php artisan key:generate

3. Configure DB

   Update `.env` with database credentials, then run migrations:

   php artisan migrate --seed

4. Run locally

   php -S 127.0.0.1:8000 -t public

Optional (Docker / Sail):

   ./vendor/bin/sail up -d

Notes:
- Review `setup.sh` before running; it automates setup on Unix-like systems.
- Seeders are optional but useful for local development.

---

## What you'll find

- Modular architecture and Filament admin panels
- Livewire-driven storefront components (cart, checkout, product pages)
- Inventory, orders, coupons, and pluggable payment gateways
- Dropshipping support, abandoned cart tracking, and multi-currency foundations

---

## Related projects

| Project | Repository |
|---|---|
| Accounting | https://github.com/liberu-accounting/accounting-laravel |
| Automation | https://github.com/liberu-automation/automation-laravel |
| Billing | https://github.com/liberu-billing/billing-laravel |
| Boilerplate | https://github.com/liberusoftware/boilerplate |
| Browser Game | https://github.com/liberu-browser-game/browser-game-laravel |
| CMS | https://github.com/liberu-cms/cms-laravel |
| Control Panel | https://github.com/liberu-control-panel/control-panel-laravel |
| CRM | https://github.com/liberu-crm/crm-laravel |
| Ecommerce | https://github.com/liberu-ecommerce/ecommerce-laravel |
| Genealogy | https://github.com/liberu-genealogy/genealogy-laravel |
| Maintenance | https://github.com/liberu-maintenance/maintenance-laravel |
| Real Estate | https://github.com/liberu-real-estate/real-estate-laravel |
| Social Network | https://github.com/liberu-social-network/social-network-laravel |

---

## Contributing

We welcome contributions. A suggested workflow:
- Fork → create a focused feature branch → open a pull request targeting `main`.
- Include tests for new behavior and keep commits small and descriptive.
- CI runs on push — ensure the `tests` and `install` workflows pass.

Please open issues to discuss larger changes before implementing.

---

## License

MIT — see the `LICENSE` file for details.

---

If you'd like this README to include usage examples, architecture diagrams, API documentation, or a developer setup checklist for specific environments (WSL, Sail, Docker Desktop), tell me which sections you'd like expanded and I will add them.
