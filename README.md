# Liberu Ecommerce

[![Install](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/install.yml/badge.svg)](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/install.yml) [![Tests](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/tests.yml) [![Docker](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/main.yml/badge.svg)](https://github.com/liberu-ecommerce/ecommerce-laravel/actions/workflows/main.yml) [![Codecov](https://codecov.io/gh/liberu-ecommerce/ecommerce-laravel/branch/main/graph/badge.svg)](https://codecov.io/gh/liberu-ecommerce/ecommerce-laravel)

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A modular, production-ready e-commerce platform built with Laravel, Livewire and Filament. Designed for extensibility and developer productivity.

Key technologies: PHP · Laravel · Livewire · Filament

---

## Quick start

Prerequisites: PHP 8.3+, Composer, (optional) Docker.

1. Clone the repo

   git clone https://github.com/liberu-ecommerce/ecommerce-laravel.git

2. Install dependencies and prepare environment

   composer install
   cp .env.example .env
   php artisan key:generate

3. Database (local)

   Configure `.env` with your DB connection, then run migrations and optionally seed:

   php artisan migrate --seed

4. Run (local)

   php -S 127.0.0.1:8000 -t public

Or with Sail (Docker-based):

   ./vendor/bin/sail up -d

Or build the included Docker image:

   docker build -t ecommerce-laravel .
   docker run -p 8000:8000 ecommerce-laravel

Notes:
- The provided `setup.sh` can automate setup on Unix-like systems; review it before running. On Windows use the equivalent commands above.
- Seeding is optional; the setup script may run seeders.

---

## Features (high level)

- Modular architecture for reusable components
- Livewire-driven interactive UI
- Filament-powered admin panels
- Inventory, orders, coupons, and payments (pluggable gateways)
- Multi-currency and localization-ready structure

---

## Related projects

| Project | Repository |
|---|---:|
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

Contributions are welcome. Please open issues for bugs or feature requests and submit pull requests from feature branches. Keep changes focused and include tests where appropriate.

Suggested workflow:
- Fork → feature branch → PR targeting `main`
- Write tests for new functionality
- Keep commits small and descriptive

---

## License

MIT — see the `LICENSE` file for details.

---

If you'd like the README to include usage examples, architecture diagrams, or API docs, tell me which sections you'd like expanded and I will add them.
