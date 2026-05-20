# System Architecture Overview

This document describes the architecture of the ecommerce platform and how the major features work together in production.

## 1. Platform Overview

The project is a Laravel 12 ecommerce application with three primary surfaces:

- Storefront web experience (Blade + Livewire) for customers.
- Filament admin panels for merchandising and operations.
- Authenticated API endpoints (Sanctum) for product, collection, and dropshipping operations.

### Core technology stack

- Backend: Laravel 12 (`composer.json`)
- Admin: Filament v5 (`composer.json`)
- Realtime UI components: Livewire v4 (`composer.json`)
- Auth/API security: Sanctum (`composer.json`)
- Frontend build: Vite + Tailwind (`package.json`)
- Query filtering: Spatie Query Builder (`composer.json`)

## 2. High-Level Layering

### Presentation layer

- Web routes and storefront controllers (`routes/web.php`, `app/Http/Controllers/Frontend/*`)
- Admin resources/widgets/pages (`app/Filament/*`)
- API routes/controllers (`routes/api.php`, `app/Http/Controllers/Api/*`)

### Application/service layer

- Checkout orchestration (`app/Http/Controllers/CheckoutController.php`)
- Shipping, tax, coupons (`app/Services/ShippingService.php`, `app/Services/TaxService.php`, `app/Services/CouponService.php`)
- Dropshipping integration (`app/Services/DropshippingService.php`)
- Recommendations and segmentation (`app/Services/ProductRecommendationService.php`, `app/Services/CustomerSegmentationService.php`)

### Domain/data layer

- Eloquent models in `app/Models/*`
- Migrations in `database/migrations/*`
- Queue jobs and notifications for async side effects (`app/Jobs/*`, `app/Notifications/*`)

## 3. Core Domain Model

## 3.1 Catalog and Merchandising

### Product

`app/Models/Product.php` is the center of the catalog domain.

It contains:

- Core merchandising data: name, slug, descriptions, price.
- Basic inventory fields: `inventory_count`, `low_stock_threshold`.
- SEO fields: `meta_title`, `meta_description`, `meta_keywords`.
- Downloadable product support: `is_downloadable`, file metadata.
- Flexible pricing support: fixed/free/donation-related fields.

Key relationships:

- Category: belongs to `ProductCategory`
- Collections: belongs to many `ProductCollection` via `collection_items`
- Tags, images, variants, options
- Cart items, order items, reviews, ratings
- Related merchandising: cross-sells, upsells, related products

### Product variants

`app/Models/ProductVariant.php` provides per-variant SKU and option-level merchandising:

- SKU, title, price, compare-at-price
- Option values (`option1`, `option2`, `option3`)
- Variant-level inventory quantity and shipping flags

### Categories and collections

- `app/Models/ProductCategory.php` supports category hierarchy (`parent_category_id`) and SEO metadata.
- `app/Models/ProductCollection.php` groups products for curated sets and supports quantity in the pivot (`collection_items`).

## 3.2 Inventory Domain

The project contains two inventory patterns:

### A) Product-level counter (actively used by checkout/cart)

- `products.inventory_count`
- `products.low_stock_threshold`
- Migration: `database/migrations/2024_02_19_132552_add_inventory_count_to_products.php`

This is currently the primary source for cart and checkout stock checks.

### B) Multi-location inventory subsystem (advanced model)

- `InventoryLocation`: physical/fulfillment nodes (`app/Models/InventoryLocation.php`)
- `InventoryItem`: stock-tracked unit linked to product (`app/Models/InventoryItem.php`)
- `InventoryLevel`: per-item per-location quantities (`app/Models/InventoryLevel.php`)
- `InventoryAdjustment`: adjustment audit trail (`app/Models/InventoryAdjustment.php`)

Quantity states at each location:

- `available`: sellable now
- `reserved`: held for checkout
- `committed`: allocated to order
- `on_hand`: physically present
- `incoming`: expected replenishment

Lifecycle methods are defined in `InventoryLevel`:

- `reserve()`
- `commit()`
- `fulfill()`
- `adjustQuantity()`

Operational logging:

- Product-level adjustments and order decrements use `InventoryLog` (`app/Models/InventoryLog.php`)
- Scheduled low-stock checks use command `inventory:check-low-stock` (`app/Console/Commands/CheckLowStockItems.php`)

## 3.3 Customer and Segmentation Domain

### Customer model

`app/Models/Customer.php` holds customer profile data and relationships to:

- Orders
- Reviews/ratings
- Groups and analytics-related entities

### Customer segmentation and metrics

- Segments: `app/Models/CustomerSegment.php`
- Metrics: `app/Models/CustomerMetric.php`
- Service API: `app/Services/CustomerSegmentationService.php`

These features support targeted marketing and behavior-based grouping.

## 3.4 Cart and Checkout Domain

### Cart

`app/Http/Controllers/CartController.php` implements session-based carts:

- Add/update/remove/clear cart items
- Coupon application and removal
- Stock checks against `Product.inventory_count`

### Checkout orchestration

`app/Http/Controllers/CheckoutController.php` is the main transaction coordinator:

1. Validates customer and shipping/payment input.
2. Validates stock for each cart line.
3. Calculates subtotal, shipping, discounts, and tax.
4. Creates `Order` and `OrderItem` records.
5. Executes payment via gateway factory.
6. Generates download tokens for downloadable order items.
7. Decrements product inventory atomically.
8. Logs inventory changes.
9. Clears cart/coupon session state.

Support services:

- Shipping logic: `app/Services/ShippingService.php`
- Tax logic: `app/Services/TaxService.php`
- Coupon validation: `app/Services/CouponService.php`

## 3.5 Orders and Post-Purchase Domain

### Order core

`app/Models/Order.php` contains:

- Customer and recipient fields
- Payment, shipping, and status fields
- Dropshipping metadata and supplier responses
- Financial totals (`total_amount`, shipping, tax, discount)

Order lines:

- `app/Models/OrderItem.php`
- Includes downloadable link metadata for digital fulfillment

Additional lifecycle entities:

- `OrderEvent` (`app/Models/OrderEvent.php`)
- `OrderStatusHistory` (`app/Models/OrderStatusHistory.php`)
- `OrderNote` (`app/Models/OrderNote.php`)

Customer order history UI:

- `app/Http/Controllers/OrderHistoryController.php`

## 3.6 Payments and Billing

Payment handling uses gateway abstraction:

- Factory: `app/Factories/PaymentGatewayFactory.php`
- Service wrapper: `app/Services/PaymentGatewayService.php`
- Checkout integration: `CheckoutController` calls Stripe/PayPal paths

Current route-level payment/subscription endpoints are defined in `routes/web.php`.

## 3.7 Dropshipping Integration

Dropshipping is first-class for selected orders.

- Configuration: `config/dropshipping.php`
- Service: `app/Services/DropshippingService.php`
- API controller: `app/Http/Controllers/Api/DropshippingController.php`
- Async job dispatch: `app/Jobs/DispatchDropshippingOrder.php`

Flow:

1. Checkout marks order as dropshipped.
2. Job is queued with selected supplier.
3. Supplier payload is transformed as needed (for example DropXL).
4. Supplier response is persisted on the order.
5. Status is updated to `supplier_placed` or `supplier_failed`.
6. Failure notifications are sent to admins.

## 3.8 Recommendations, Analytics, and Growth Features

### Recommendations

Two services exist:

- `RecommendationService` (legacy/simple behavior-based)
- `ProductRecommendationService` (interaction and collaborative logic)

The advanced recommendation service supports:

- Personalized recommendations
- Trending products
- Also-bought recommendations
- Similar product recommendations
- Interaction tracking (view, add_to_cart, purchase)

### Analytics and admin insights

- Inventory stats widget: `app/Filament/Admin/Widgets/InventoryStatsWidget.php`
- Customer analytics widgets/resources under `app/Filament/Admin/Widgets/*` and `app/Filament/Resources/*`

### Recovery and marketing extensions

From implemented feature docs and models:

- Abandoned cart recovery
- Gift registry
- Product taxonomy and attributes
- Customer segmentation campaigns

See: `docs/SHOPIFY_MAGENTO_FEATURES.md`

## 4. Interfaces and Delivery Channels

### Storefront web routes

Core browsing and checkout flows are defined in `routes/web.php`:

- Product/category/collection/tag pages
- Cart and checkout endpoints
- Reviews/ratings
- Payments and subscriptions
- Order history

### API routes

Defined in `routes/api.php`:

- Product CRUD/search/filter APIs
- Collection management APIs
- Dropshipping operational APIs

All grouped under Sanctum auth middleware where required.

### Admin interfaces (Filament)

Filament resources/pages are organized in:

- `app/Filament/App/Resources/*`
- `app/Filament/Admin/Resources/*`
- `app/Filament/Admin/Widgets/*`

These handle operational CRUD and analytics visibility for catalog, inventory, orders, and customers.

## 5. Security, Queueing, and Operational Concerns

### Security controls

- Standard Laravel validation throughout controllers.
- CSRF protection for web state-changing requests.
- Sanctum auth for protected API endpoints.
- Policy classes exist for core entities in `app/Policies/*`.

### Queue and async processing

- Dropshipping order dispatch is queued (`DispatchDropshippingOrder`).
- Several notifications are queueable (`ShouldQueue`).

### Operational commands

- Inventory low-stock command exists (`inventory:check-low-stock`).
- Command scheduler is currently minimal and can be expanded in `app/Console/Kernel.php`.

## 6. Modular Extension Architecture

The project has an internal module system:

- Module manager: `app/Modules/ModuleManager.php`
- Module provider: `app/Modules/ModuleServiceProvider.php`
- CLI tooling: `app/Console/Commands/ModuleCommand.php`
- Config: `config/modules.php`

Capabilities:

- Auto-discovery of modules under `app/Modules`
- Module route/view/migration/config registration
- Enable/disable/install/uninstall lifecycle

## 7. Request-to-Data Flows

### Product browse flow

1. Client hits `Frontend\ProductController`.
2. Query builder filters/sorts products.
3. Eloquent returns product rows + relationships.
4. Blade view renders product lists/details.

### Checkout flow

1. Session cart is validated.
2. Pricing components are computed.
3. Order + order items are persisted.
4. Payment is processed.
5. Inventory is decremented and logged.
6. Notifications and optional dropship queue run.

### Dropship fulfillment flow

1. Order flagged as dropshipped.
2. `DispatchDropshippingOrder` job executes.
3. `DropshippingService` calls supplier API.
4. Supplier response and status are persisted.

## 8. Current Architectural Reality and Future Direction

### Current reality

- Core commerce flow is stable and production-oriented.
- Checkout currently relies on `Product.inventory_count` as stock source.
- Multi-location inventory model is present and partially integrated at model/service level.

### Recommended direction

To fully leverage multi-location inventory:

1. Move checkout stock checks from `products.inventory_count` to `inventory_levels.available`.
2. Use `reserve -> commit -> fulfill` transitions per location.
3. Keep product-level counters as derived/cache values or deprecate them.
4. Ensure adjustment logging keeps both level and item references consistent.

## 9. Key File Map (Quick Reference)

- Catalog core: `app/Models/Product.php`
- Variants: `app/Models/ProductVariant.php`
- Categories: `app/Models/ProductCategory.php`
- Collections: `app/Models/ProductCollection.php`
- Cart: `app/Http/Controllers/CartController.php`
- Checkout: `app/Http/Controllers/CheckoutController.php`
- Orders: `app/Models/Order.php`, `app/Models/OrderItem.php`
- Inventory (simple): `products.inventory_count` fields
- Inventory (advanced): `InventoryLocation`, `InventoryItem`, `InventoryLevel`, `InventoryAdjustment`
- Dropshipping: `app/Services/DropshippingService.php`, `app/Jobs/DispatchDropshippingOrder.php`
- API entry points: `routes/api.php`
- Web entry points: `routes/web.php`
- Admin surfaces: `app/Filament/*`

---

This architecture supports both classic ecommerce operations and modern growth features (segmentation, recommendations, dropshipping, analytics) while leaving room to converge inventory on a full multi-location fulfillment model.
