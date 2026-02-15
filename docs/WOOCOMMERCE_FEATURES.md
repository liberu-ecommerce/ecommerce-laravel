# WooCommerce Feature Implementation

This document describes the newly implemented WooCommerce features and common plugin functionality added to the Laravel ecommerce platform.

## Features Overview

### 1. Tax System

A comprehensive tax calculation system with support for multiple tax classes, rates, and location-based taxation.

**Database Tables:**
- `tax_classes` - Tax categories (Standard, Reduced, Zero-rated)
- `tax_rates` - Tax rates by location (country, state, city, zip)

**Models:**
- `TaxClass` - Tax classification model
- `TaxRate` - Tax rate model with location matching
- `TaxCalculator` (Service) - Calculate taxes for cart and products

**Features:**
- Multiple tax classes per product
- Location-based tax calculation (country, state, city, zip)
- Compound taxes support
- Shipping tax support
- Tax priority system
- Tax-inclusive/exclusive pricing

**Usage:**
```php
use App\Services\TaxCalculator;

$calculator = new TaxCalculator();
$taxData = $calculator->calculateCartTax($cartItems, $shippingAddress, $shippingCost);
// Returns: ['total' => 10.50, 'lines' => [...]]
```

---

### 2. Product Cross-sells, Upsells, and Related Products

Display related products to increase average order value.

**Database Tables:**
- `product_cross_sells` - Products to show in cart
- `product_upsells` - Premium alternatives
- `product_related` - Related/similar products

**Features:**
- Cross-sell products shown in cart
- Upsell products shown on product page
- Related products for recommendations
- Sort order support

**Usage:**
```php
$product = Product::find(1);
$crossSells = $product->crossSells; // Show these in cart
$upsells = $product->upsells; // Show as premium options
$related = $product->relatedProducts; // Show as similar items
```

---

### 3. Stock Notifications & Backorders

Allow customers to subscribe to stock availability notifications.

**Database Tables:**
- `stock_notifications` - Customer notification subscriptions
- Products and variants updated with backorder fields

**Models:**
- `StockNotification` - Notification subscription tracking

**Features:**
- Email notifications when products are back in stock
- Backorder support (allow purchases when out of stock)
- Stock status tracking (in_stock, out_of_stock, on_backorder)
- Low stock notifications
- Price drop notifications

**Usage:**
```php
// Subscribe to stock notification
StockNotification::create([
    'product_id' => $productId,
    'email' => 'customer@example.com',
    'notification_type' => 'back_in_stock'
]);

// Get pending notifications
$pending = StockNotification::getPendingForProduct($productId);
```

---

### 4. Product Attributes System

Global attribute management system for consistent product specifications.

**Database Tables:**
- `product_attributes` - Global attributes (Color, Size, Material)
- `product_attribute_values` - Attribute values (Red, Blue, Large)
- `product_attribute_product` - Product-attribute relationships

**Models:**
- `ProductAttribute` - Attribute definitions
- `ProductAttributeValue` - Attribute value options

**Features:**
- Global attributes reusable across products
- Multiple attribute types (select, text, color, image)
- Variant generation from attributes
- Archive/filter support
- Color swatches and image swatches

**Usage:**
```php
$attribute = ProductAttribute::create([
    'name' => 'Color',
    'slug' => 'color',
    'type' => 'color',
]);

$attribute->values()->create([
    'value' => 'Red',
    'slug' => 'red',
    'color_code' => '#FF0000',
]);
```

---

### 5. Refund & Return Management (RMA)

Complete refund processing and return merchandise authorization system.

**Database Tables:**
- `refunds` - Refund requests and processing
- `refund_items` - Line items being refunded
- `return_requests` - RMA tracking
- `return_request_items` - Items being returned

**Models:**
- `Refund` - Refund processing
- `RefundItem` - Individual refunded items
- `ReturnRequest` - Return authorization
- `ReturnRequestItem` - Return line items

**Features:**
- Full and partial refunds
- Automatic inventory restocking
- RMA number generation
- Return tracking
- Multiple refund methods (original payment, store credit, manual)
- Order refund tracking

**Usage:**
```php
// Create a refund
$refund = Refund::create([
    'order_id' => $orderId,
    'amount' => 50.00,
    'reason' => 'Customer request',
    'restock_items' => true,
]);

// Process refund
$refund->process($userId);

// Create return request
$return = ReturnRequest::create([
    'order_id' => $orderId,
    'customer_id' => $customerId,
    'reason' => 'Defective item',
]);

$return->approve($adminId);
```

---

### 6. Product Bundles

Create product bundles/kits with special pricing.

**Database Tables:**
- `product_bundles` - Bundle definitions
- `product_bundle_items` - Products in bundle

**Models:**
- `ProductBundle` - Bundle container
- `ProductBundleItem` - Bundle components

**Features:**
- Multiple products in a bundle
- Percentage or fixed discounts
- Optional items
- Stock checking for all bundle items
- Automatic pricing calculation

**Usage:**
```php
$bundle = ProductBundle::create([
    'product_id' => $bundleProductId,
    'name' => 'Starter Kit',
    'discount_percentage' => 15,
]);

$bundle->items()->create([
    'product_id' => $itemProductId,
    'quantity' => 2,
]);

$savings = $bundle->getSavings();
$price = $bundle->getBundlePrice();
```

---

### 7. Order Notes & Timeline

Track order history, notes, and events.

**Database Tables:**
- `order_notes` - Internal and customer notes
- `order_status_history` - Status change tracking
- `order_events` - Event timeline

**Models:**
- `OrderNote` - Note management
- `OrderStatusHistory` - Status tracking
- `OrderEvent` - Event logging

**Features:**
- Internal notes (staff only)
- Customer notes (visible to customer)
- System notes (automated)
- Status change history
- Event timeline with metadata

**Usage:**
```php
// Add customer note
OrderNote::createCustomerNote($orderId, 'Package will arrive tomorrow');

// Add internal note
OrderNote::createInternalNote($orderId, 'Customer requested gift wrap', $userId);

// Log event
OrderEvent::log($orderId, 'shipped', 'Order shipped via UPS', ['tracking' => '1Z999...']);
```

---

### 8. Pre-orders

Accept orders for products not yet released.

**Database Tables:**
- `preorders` - Pre-order tracking
- Products updated with pre-order fields

**Models:**
- `Preorder` - Pre-order management

**Features:**
- Pre-order availability windows
- Release date tracking
- Pre-order quantity limits
- Charge now or on release options
- Customer notifications on release

**Usage:**
```php
// Enable pre-orders on product
$product->update([
    'is_preorder' => true,
    'preorder_release_date' => '2024-06-01',
    'preorder_limit' => 100,
]);

// Track pre-order
$preorder = Preorder::create([
    'order_id' => $orderId,
    'product_id' => $productId,
    'quantity' => 2,
    'expected_release_date' => $releaseDate,
]);

// Release pre-order
$preorder->release();
```

---

### 9. Multi-Currency Support

Support multiple currencies with automatic conversion.

**Database Tables:**
- `currencies` - Currency definitions
- `product_currency_prices` - Product prices in different currencies

**Models:**
- `Currency` - Currency management
- `ProductCurrencyPrice` - Multi-currency pricing

**Features:**
- Multiple active currencies
- Exchange rate management
- Currency-specific formatting
- Product prices in multiple currencies
- Order currency tracking

**Usage:**
```php
// Create currency
$currency = Currency::create([
    'code' => 'EUR',
    'name' => 'Euro',
    'symbol' => '€',
    'exchange_rate' => 0.85,
]);

// Format price
$formatted = $currency->formatPrice(100); // "€100.00"

// Convert amounts
$converted = $currency->convertFromBase(100); // 85.00 EUR

// Set product price in currency
ProductCurrencyPrice::create([
    'product_id' => $productId,
    'currency_code' => 'EUR',
    'price' => 85.00,
]);
```

---

### 10. Loyalty Points & Rewards Program

Customer loyalty program with points, tiers, and rewards.

**Database Tables:**
- `loyalty_programs` - Program definitions
- `loyalty_points` - Customer point balances
- `loyalty_point_transactions` - Point history
- `loyalty_tiers` - Tier levels
- `loyalty_rewards` - Rewards catalog
- `loyalty_reward_redemptions` - Redemption tracking

**Models:**
- `LoyaltyProgram` - Program management
- `LoyaltyPoints` - Point balance tracking
- `LoyaltyTier` - Tier definitions
- `LoyaltyReward` - Reward catalog
- `LoyaltyRewardRedemption` - Redemption tracking

**Features:**
- Points earned per dollar spent
- Point expiration
- Tiered benefits
- Reward catalog
- Point redemption
- Multiple reward types (discounts, free products, free shipping)

**Usage:**
```php
// Create loyalty program
$program = LoyaltyProgram::create([
    'name' => 'VIP Rewards',
    'points_per_dollar' => 10,
    'points_value' => 0.01,
    'points_expiry_days' => 365,
]);

// Award points
$loyaltyPoints = LoyaltyPoints::firstOrCreate([
    'user_id' => $userId,
    'loyalty_program_id' => $program->id,
]);
$loyaltyPoints->addPoints(100, 'earned', 'Purchase reward', $orderId);

// Redeem reward
$reward = LoyaltyReward::find($rewardId);
$redemption = $reward->redeem($userId, $orderId);
```

---

### 11. Product Comparison

Allow customers to compare products side-by-side.

**Database Tables:**
- `product_comparisons` - Comparison lists
- `comparison_attributes` - Attributes to compare

**Features:**
- Guest and user comparisons
- Customizable comparison attributes
- Multiple product comparison

---

### 12. B2B Wholesale Pricing

Wholesale customer management with tiered pricing.

**Database Tables:**
- `wholesale_groups` - Wholesale customer groups
- `wholesale_price_tiers` - Quantity-based pricing
- `quote_requests` - Quote management
- Customers updated with wholesale fields

**Models:**
- `WholesaleGroup` - Group management
- `WholesalePriceTier` - Tiered pricing
- `QuoteRequest` - Quote system

**Features:**
- Wholesale customer groups
- Approval workflow
- Tiered pricing by quantity
- Min/max order quantities
- Quote request system
- Hide retail pricing option

**Usage:**
```php
// Create wholesale group
$group = WholesaleGroup::create([
    'name' => 'Platinum Wholesale',
    'discount_percentage' => 30,
    'requires_approval' => true,
]);

// Set tiered pricing
WholesalePriceTier::create([
    'product_id' => $productId,
    'wholesale_group_id' => $group->id,
    'min_quantity' => 10,
    'price' => 8.50,
]);

// Get price for quantity
$price = WholesalePriceTier::getPriceForQuantity($productId, 25, null, $groupId);

// Create quote request
$quote = QuoteRequest::create([
    'user_id' => $userId,
    'items' => $cartItems,
    'notes' => 'Bulk order inquiry',
]);
```

---

## Database Migrations

All migrations are located in `database/migrations/` with the prefix `2024_02_15_*`:

1. `000001_create_tax_tables.php` - Tax system
2. `000002_create_product_relations_tables.php` - Cross-sells/upsells
3. `000003_create_stock_notifications_table.php` - Stock alerts
4. `000004_create_product_attributes_tables.php` - Attributes
5. `000005_create_refunds_and_returns_tables.php` - Refunds/RMA
6. `000006_create_product_bundles_tables.php` - Product bundles
7. `000007_create_order_notes_and_history_tables.php` - Order tracking
8. `000008_create_preorders_table.php` - Pre-orders
9. `000009_create_currencies_tables.php` - Multi-currency
10. `000010_create_loyalty_program_tables.php` - Loyalty program
11. `000011_create_product_comparison_tables.php` - Product comparison
12. `000012_create_wholesale_pricing_tables.php` - B2B wholesale

## Installation

Run migrations to install all features:

```bash
php artisan migrate
```

## Configuration

Add to your `.env` file:

```env
# Tax settings
TAX_DISPLAY_PRICES_WITH_TAX=false

# Currency settings
DEFAULT_CURRENCY=USD

# Loyalty program
LOYALTY_ENABLED=true
```

## Admin Panel

Filament admin resources are provided for managing:
- Tax Classes
- Tax Rates (coming soon)
- Loyalty Programs (coming soon)
- Wholesale Groups (coming soon)

Access at `/admin` after logging in.

## API Integration

All models are designed to work with Laravel's REST API and can be easily integrated with your frontend application.

## Testing

Run the test suite:

```bash
php artisan test
```

## Support

For issues or questions, please open an issue in the GitHub repository.

## License

MIT License - see LICENSE file for details.
