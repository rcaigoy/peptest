# MySQL-Based Product Architecture

## Overview

This architecture separates product fetching logic from presentation, allowing the same MySQL queries to be used by both the JSON API endpoint and WordPress theme templates.

## File Structure

```
peptidology-new/
├── logic/
│   └── get-products.php          # Core product fetching logic
└── api/
    └── products.php               # JSON API endpoint

wp-content/themes/peptidology4/woocommerce/
└── archive-product.php            # Product listing template
```

## Architecture Diagram

```
┌─────────────────────────────────────┐
│   peptidology-new/logic/            │
│   get-products.php                  │
│                                     │
│   get_products_from_mysql()         │
│   - Connects to MySQL directly      │
│   - Fetches all products            │
│   - Returns array of objects        │
└─────────────────────────────────────┘
                  │
                  ├──────────────────────────────────┐
                  │                                  │
                  ▼                                  ▼
┌─────────────────────────────────┐  ┌─────────────────────────────────┐
│  peptidology-new/api/           │  │  wp-content/themes/             │
│  products.php                   │  │  peptidology4/woocommerce/      │
│                                 │  │  archive-product.php            │
│  - Includes get-products.php    │  │                                 │
│  - Calls get_products_from_     │  │  - Includes get-products.php    │
│    mysql()                      │  │  - Calls get_products_from_     │
│  - Returns JSON response        │  │    mysql()                      │
│                                 │  │  - Renders HTML output          │
└─────────────────────────────────┘  └─────────────────────────────────┘
                  │                                  │
                  ▼                                  ▼
          JSON API Response              HTML Product Listing
```

## Core Logic: `get-products.php`

### Database Configuration
```php
$db_host = 'localhost';
$db_name = 'defaultdb';
$db_user = 'localuser';
$db_pass = 'guest';
$table_prefix = 'wp_';
```

### Function: `get_products_from_mysql()`

**Returns:**
```php
[
    'products' => [
        [
            'id' => 3051,
            'name' => '5-Amino-1MQ  50mg',
            'slug' => '5-amino-1mq',
            'type' => 'variable',
            'status' => 'publish',
            'stock_status' => 'instock',
            'price' => 98.00,
            'default_variation_id' => 3054,
            'thumbnail_id' => 12345,
            'image_url' => 'http://...',
            'image_width' => 1475,
            'image_height' => 2011,
            'image_sizes' => [...],
            'categories' => ['peptides', 'metabolic-modulator'],
            'permalink' => 'http://.../product/5-amino-1mq/',
            'add_to_cart_url' => 'http://.../products/?add-to-cart=3051&variation_id=3054&quantity=1'
        ],
        // ... more products
    ],
    'total' => 38
]
```

### Database Queries (4 total)

1. **Main Products Query** - Fetches all products with metadata and default variations
2. **Categories Query** - Batch fetch all categories for all products
3. **Images Query** - Batch fetch image details and metadata for all thumbnails
4. **Count Query** - Get total number of products

## API Endpoint: `api/products.php`

**URL:** `http://yourdomain.com/peptidology-new/api/products.php`

**Response:** JSON
```json
{
    "products": [...],
    "total": 38
}
```

**Usage:**
```javascript
fetch('http://yourdomain.com/peptidology-new/api/products.php')
    .then(response => response.json())
    .then(data => {
        console.log(`Found ${data.total} products`);
        data.products.forEach(product => {
            console.log(product.name, product.price);
        });
    });
```

## Theme Template: `archive-product.php`

**Location:** `wp-content/themes/peptidology4/woocommerce/archive-product.php`

**Usage in template:**
```php
<?php
// Function is already included at top of file
$result = get_products_from_mysql();

if (isset($result['error'])) {
    echo '<p>Error loading products</p>';
} else {
    foreach ($result['products'] as $product) {
        // Render product HTML
        ?>
        <div class="col-lg-3 col-sm-6 col-6 product">
            <div class="cmn-product-crd">
                <a href="<?php echo esc_url($product['permalink']); ?>">
                    <img src="<?php echo esc_url($product['image_url']); ?>" 
                         alt="<?php echo esc_attr($product['name']); ?>">
                    <h3><?php echo esc_html($product['name']); ?></h3>
                </a>
                <a href="<?php echo esc_url($product['add_to_cart_url']); ?>">
                    Add to Cart - $<?php echo number_format($product['price'], 2); ?>
                </a>
            </div>
        </div>
        <?php
    }
}
?>
```

## Benefits of This Architecture

### 1. **Single Source of Truth**
- Product fetching logic lives in one place
- Changes to queries update both API and template automatically

### 2. **Separation of Concerns**
- Logic layer: Pure data fetching
- API layer: JSON formatting
- Template layer: HTML rendering

### 3. **Reusability**
- Same function can be used anywhere
- Easy to add new endpoints or templates

### 4. **Maintainability**
- Easier to debug (one place to fix)
- Easier to optimize (optimize once, benefit everywhere)
- Clear responsibilities per file

### 5. **Performance**
- Direct MySQL access (no WordPress query overhead)
- Optimized batch queries
- 4 queries total vs 50-100+ with standard WooCommerce

## Testing

Run the test script to verify everything works:

```bash
http://yourdomain.com/test-mysql-products.php
```

This will show:
- Execution time
- Total products found
- Detailed view of products
- Complete product list

## Future Enhancements

### 1. Move Database Config
Extract credentials to separate config file:
```php
// peptidology-new/config/database.php
return [
    'host' => 'localhost',
    'name' => 'defaultdb',
    'user' => 'localuser',
    'pass' => 'guest',
    'prefix' => 'wp_'
];
```

### 2. Add Caching
```php
function get_products_from_mysql() {
    $cache_key = 'products_mysql_cache';
    $cached = wp_cache_get($cache_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    // ... fetch from database ...
    
    wp_cache_set($cache_key, $result, '', 300); // 5 min cache
    return $result;
}
```

### 3. Add Filtering/Sorting
```php
function get_products_from_mysql($filters = []) {
    // Apply category filter
    // Apply price range filter
    // Apply sorting options
}
```

### 4. Add Pagination (if needed later)
```php
function get_products_from_mysql($per_page = 38, $page = 1) {
    $offset = ($page - 1) * $per_page;
    // Add LIMIT and OFFSET to query
}
```

## Migration Path

1. ✅ Create shared logic file
2. ✅ Update API to use shared logic
3. ✅ Update template to use shared logic
4. ⏳ Test both API and template
5. ⏳ Replace WordPress loop with direct rendering
6. ⏳ Benchmark performance improvements

## Notes

- Currently fetches ALL products (no pagination)
- Perfect for sites with <100 products
- Database credentials are hardcoded (will be moved to config)
- Function works both with and without WordPress loaded
- Automatic variation detection for variable products
- Includes all image metadata for responsive images

