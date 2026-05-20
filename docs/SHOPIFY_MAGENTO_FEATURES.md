# Shopify and Magento 2 Features Implementation

This document describes the newly implemented features from Shopify and Magento 2 (Adobe Commerce) that have been added to the Laravel ecommerce platform.

## Table of Contents

1. [Customer Segmentation](#customer-segmentation)
2. [AI-Powered Product Recommendations](#ai-powered-product-recommendations)
3. [Product Taxonomy](#product-taxonomy)
4. [Abandoned Cart Recovery](#abandoned-cart-recovery)
5. [Gift Registry](#gift-registry)
6. [Customer Analytics & Metrics](#customer-analytics--metrics)
7. [A/B Testing Framework](#ab-testing-framework)
8. [Product Performance Analytics](#product-performance-analytics)

---

## Customer Segmentation

Advanced customer segmentation allows targeting specific groups based on behavior, purchase history, and other criteria.

### Database Tables
- `customer_segments` - Segment definitions with conditions
- `customer_segment_members` - Segment membership tracking

### Models
- `CustomerSegment` - Segment management with auto-calculation

### Features
- **Condition-based segmentation** - Create rules based on:
  - Total orders count
  - Lifetime value
  - Last order date
  - Purchased specific products
  - Customer group membership
- **Match types** - Match ALL or ANY conditions
- **Auto-calculation** - Segments update automatically
- **Cached counts** - Fast segment size retrieval

### Usage

```php
use App\Models\CustomerSegment;

// Create a VIP segment
$vipSegment = CustomerSegment::create([
    'name' => 'VIP Customers',
    'description' => 'High-value customers with $1000+ lifetime value',
    'conditions' => [
        [
            'field' => 'lifetime_value',
            'operator' => '>=',
            'value' => 1000
        ]
    ],
    'match_type' => 'all',
    'is_active' => true,
]);

// Calculate members
$vipSegment->calculateMembers();

// Get segment members
$members = $vipSegment->members;
```

### Console Command

```bash
# Calculate all active segments
php artisan segments:calculate

# Calculate specific segments
php artisan segments:calculate --segment=1 --segment=2
```

### Service Usage

```php
use App\Services\CustomerSegmentationService;

$service = app(CustomerSegmentationService::class);

// Check if user is in segment
$isVip = $service->isUserInSegment($userId, $segmentId);

// Get all segments for user
$segments = $service->getUserSegments($userId);

// Get segment statistics
$stats = $service->getSegmentStats($segmentId);
// Returns: ['total_members' => 150, 'average_ltv' => 1250.00, ...]
```

---

## AI-Powered Product Recommendations

Intelligent product recommendations using collaborative filtering and behavioral analysis.

### Database Tables
- `recommendation_rules` - Recommendation engine rules
- `product_recommendations` - Product-to-product recommendations
- `product_interactions` - User interaction tracking

### Models
- `RecommendationRule` - Rule definitions
- `ProductRecommendation` - Recommendation relationships
- `ProductInteraction` - Interaction tracking

### Features
- **Collaborative filtering** - "Customers also bought"
- **Personalized recommendations** - Based on user history
- **Trending products** - Popular items tracking
- **Similar products** - Category and price-based
- **Interaction tracking** - Views, cart adds, purchases

### Usage

```php
use App\Services\ProductRecommendationService;

$service = app(ProductRecommendationService::class);

// Get personalized recommendations
$recommendations = $service->getPersonalizedRecommendations(
    userId: $userId,
    sessionId: session()->getId(),
    limit: 10
);

// Get "also bought" recommendations
$alsoBought = $service->getAlsoBoughtRecommendations($productId, 6);

// Get similar products
$similar = $service->getSimilarProducts($product, 6);

// Track interactions
$service->trackView($userId, $productId, $duration);
$service->trackAddToCart($userId, $productId);
$service->trackPurchase($userId, $productId);
```

### Console Command

```bash
# Generate recommendations using collaborative filtering
php artisan recommendations:generate
```

### Recommendation Types

1. **Personalized** - Based on user's browsing/purchase history
2. **Collaborative** - "Customers who bought X also bought Y"
3. **Similar** - Products in same category with similar price
4. **Trending** - Popular products in last 7 days

---

## Product Taxonomy

Hierarchical product categorization for better organization and discoverability.

### Database Tables
- `taxonomy_categories` - Hierarchical categories
- `product_taxonomy` - Product assignments
- `taxonomy_attributes` - Category-specific attributes
- `product_taxonomy_values` - Product attribute values

### Models
- `TaxonomyCategory` - Category management
- `TaxonomyAttribute` - Attribute definitions
- `ProductTaxonomyValue` - Product values

### Features
- **Hierarchical structure** - Nested categories with materialized paths
- **Custom attributes** - Define attributes per category
- **Filterable attributes** - Enable filtering by attributes
- **Multiple categories** - Products can belong to multiple taxonomies
- **Breadcrumb support** - Auto-generated category paths

### Usage

```php
use App\Models\TaxonomyCategory;

// Create root category
$electronics = TaxonomyCategory::create([
    'name' => 'Electronics',
    'slug' => 'electronics',
    'is_active' => true,
]);

// Create subcategory
$phones = TaxonomyCategory::create([
    'name' => 'Smartphones',
    'slug' => 'smartphones',
    'parent_id' => $electronics->id,
]);

// Add attributes
$phones->attributes()->create([
    'name' => 'Screen Size',
    'slug' => 'screen-size',
    'type' => 'select',
    'options' => ['5.5"', '6.1"', '6.7"'],
    'is_filterable' => true,
]);

// Assign product to taxonomy
$product->taxonomyCategories()->attach($phones->id, [
    'is_primary' => true
]);

// Set attribute values
ProductTaxonomyValue::create([
    'product_id' => $product->id,
    'taxonomy_attribute_id' => $attribute->id,
    'value' => '6.1"',
]);

// Get breadcrumbs
$breadcrumbs = $phones->getBreadcrumbs();
```

---

## Abandoned Cart Recovery

Automated campaigns to recover abandoned carts with email/SMS and discounts.

### Database Tables
- `abandoned_carts` - Enhanced with recovery fields
- `cart_recovery_campaigns` - Campaign definitions
- `cart_recovery_attempts` - Tracking sends and conversions

### Models
- `CartRecoveryCampaign` - Campaign management
- `CartRecoveryAttempt` - Attempt tracking

### Features
- **Multiple channels** - Email, SMS, or both
- **Delay triggers** - Send after X minutes
- **Discount incentives** - Automatic discount codes
- **Condition-based** - Target specific cart values
- **Conversion tracking** - Track clicks and conversions

### Usage

```php
use App\Models\CartRecoveryCampaign;

// Create recovery campaign
$campaign = CartRecoveryCampaign::create([
    'name' => 'First Hour Recovery',
    'trigger_type' => 'email',
    'delay_minutes' => 60,
    'email_subject' => 'Complete your purchase and save 10%!',
    'email_body' => 'You left items in your cart...',
    'include_discount' => true,
    'discount_type' => 'percentage',
    'discount_value' => 10,
    'conditions' => [
        ['field' => 'cart_value', 'operator' => '>=', 'value' => 50]
    ],
]);

// Check if campaign applies
if ($campaign->meetsConditions($abandonedCart)) {
    // Send recovery email
}
```

---

## Gift Registry

Complete gift registry system for weddings, baby showers, and other events.

### Database Tables
- `gift_registries` - Registry definitions
- `gift_registry_items` - Products in registry
- `gift_registry_purchases` - Purchase tracking

### Models
- `GiftRegistry` - Registry management
- `GiftRegistryItem` - Item tracking
- `GiftRegistryPurchase` - Purchase records

### Features
- **Multiple event types** - Wedding, baby, birthday, etc.
- **Privacy levels** - Public, private, link-only
- **Access codes** - For private registries
- **Completion tracking** - Show progress
- **Anonymous purchases** - Option to hide purchaser
- **Shipping address** - Store delivery information

### Usage

```php
use App\Models\GiftRegistry;

// Create registry
$registry = GiftRegistry::create([
    'user_id' => $userId,
    'name' => 'John & Jane\'s Wedding',
    'type' => 'wedding',
    'event_date' => '2025-06-15',
    'privacy' => 'public',
    'is_active' => true,
]);

// Add items
$item = $registry->items()->create([
    'product_id' => $productId,
    'quantity_requested' => 2,
    'priority' => 1, // Must have
]);

// Mark as purchased
$item->markPurchased(
    quantity: 1,
    orderId: $orderId,
    purchaserName: 'Sarah Smith',
    purchaserEmail: 'sarah@example.com',
    anonymous: false
);

// Get completion percentage
$completion = $registry->getCompletionPercentage(); // 50.00
```

---

## Customer Analytics & Metrics

Comprehensive customer lifetime value and behavior tracking.

### Database Tables
- `customer_metrics` - Customer LTV and statistics
- `conversion_funnels` - Funnel definitions
- `conversion_events` - Event tracking

### Models
- `CustomerMetric` - Metric tracking

### Features
- **Lifetime Value (LTV)** - Total customer spend
- **Average Order Value (AOV)** - Average per order
- **Purchase frequency** - Total orders
- **Recency tracking** - Days since last purchase
- **Customer segmentation** - Auto-categorization (new, active, at_risk, churned, vip)
- **Retention scoring** - 0-100 score
- **Predictions** - Next order value and date

### Usage

```php
use App\Models\CustomerMetric;

// Get or create metrics
$metric = CustomerMetric::firstOrCreate(['user_id' => $userId]);

// Recalculate all metrics
$metric->recalculate();

// Access metrics
$ltv = $metric->lifetime_value; // 1250.50
$aov = $metric->average_order_value; // 125.05
$segment = $metric->customer_segment; // "vip"
$score = $metric->retention_score; // 85
```

### Console Command

```bash
# Update all customer metrics
php artisan metrics:update-customers

# Update specific customers
php artisan metrics:update-customers --user=1 --user=2
```

---

## A/B Testing Framework

Built-in A/B testing for optimizing conversions.

### Database Tables
- `ab_tests` - Test definitions
- `ab_test_assignments` - User/session assignments

### Models
- `ABTest` - Test management
- `ABTestAssignment` - Assignment tracking

### Features
- **Multiple test types** - Page, feature, price, content, checkout
- **Traffic allocation** - Control test exposure
- **Variant management** - Multiple variants per test
- **Conversion tracking** - Track goals and revenue
- **Statistical analysis** - Built-in conversion rates

### Usage

```php
use App\Services\ABTestingService;

$service = app(ABTestingService::class);

// Create test
$test = $service->createTest(
    name: 'Checkout Button Color',
    type: 'feature',
    variants: [
        ['name' => 'control', 'weight' => 50],
        ['name' => 'red_button', 'weight' => 50],
    ],
    description: 'Test red vs blue checkout button'
);

// Start test
$service->startTest($test->id);

// Get variant for user
$variant = $service->getVariant('Checkout Button Color', $userId);

// Check variant
if ($service->isVariant('Checkout Button Color', 'red_button')) {
    // Show red button
}

// Track conversion
$service->trackConversion('Checkout Button Color', $orderTotal);

// Get results
$results = $service->getTestResults($test->id);
// Returns conversion rates and statistics per variant

// End test with winner
$service->endTest($test->id, 'red_button');
```

---

## Product Performance Analytics

Track product views, conversions, and performance.

### Database Tables
- `product_performance` - Daily product metrics

### Models
- `ProductPerformance` - Performance tracking

### Features
- **Daily metrics** - Views, cart adds, purchases
- **Revenue tracking** - Per product revenue
- **Conversion rates** - View-to-purchase conversion
- **Return rates** - Product return tracking
- **Automatic calculation** - Auto-update rates

### Usage

```php
use App\Models\ProductPerformance;

// Record view
ProductPerformance::recordView($productId);

// Record add to cart
ProductPerformance::recordAddToCart($productId);

// Record purchase
ProductPerformance::recordPurchase($productId, $revenue);

// Get performance data
$performance = ProductPerformance::where('product_id', $productId)
    ->where('date', today())
    ->first();

$views = $performance->views;
$conversionRate = $performance->conversion_rate; // Auto-calculated
```

---

## Installation

Run migrations to install all features:

```bash
php artisan migrate
```

## Configuration

No additional configuration required. All features work out of the box.

## Scheduled Tasks

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Update customer metrics daily
    $schedule->command('metrics:update-customers')->daily();
    
    // Calculate segments daily
    $schedule->command('segments:calculate')->daily();
    
    // Generate recommendations weekly
    $schedule->command('recommendations:generate')->weekly();
}
```

## API Usage

All models are REST API-ready and can be exposed via Laravel's resource controllers.

## Admin Panel

Filament admin resources can be created for managing:
- Customer segments
- Recommendation rules
- Gift registries
- A/B tests
- Cart recovery campaigns

## Support

For issues or questions, please open an issue in the GitHub repository.

## License

MIT License - see LICENSE file for details.
